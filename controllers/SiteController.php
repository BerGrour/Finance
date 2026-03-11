<?php

namespace app\controllers;

use Yii;
use yii\filters\AccessControl;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;
use app\models\SignupForm;
use app\models\Account\Account;
use app\models\Earnings\Earnings;
use app\models\Expenses\Expenses;

/**
 * Контроллер для главной страницы и основных действий сайта
 */
class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Получение данных за последние 30 дней
     *
     * @param float $currentBalance Текущий общий баланс
     * @return array Массив с данными
     */
    private function getLast30DaysData($currentBalance = 0)
    {
        // @TODO: Перенести некоторые вычисления в модели
        $now = time();
        
        // Последние 30 дней
        $currentStart = strtotime('-30 days', $now);
        $currentEnd = $now;
        
        // Предыдущие 30 дней (31-60 дней назад)
        $previousStart = strtotime('-60 days', $now);
        $previousEnd = $currentStart;
        
        // Доходы за последние 30 дней
        $periodEarnings = Earnings::findByUser()
            ->andWhere(['status' => Earnings::STATUS_RECEIVED, 'is_counted_in_stats' => 1])
            ->andWhere(['>=', 'date_time', $currentStart])
            ->andWhere(['<=', 'date_time', $currentEnd])
            ->sum('amount') ?: 0;
        
        // Расходы за последние 30 дней
        $periodExpenses = Expenses::findByUser()
            ->andWhere(['status' => Expenses::STATUS_RECEIVED, 'is_counted_in_stats' => 1])
            ->andWhere(['>=', 'date_time', $currentStart])
            ->andWhere(['<=', 'date_time', $currentEnd])
            ->sum('amount') ?: 0;
        
        // Доходы за предыдущие 30 дней
        $previousPeriodEarnings = Earnings::findByUser()
            ->andWhere(['status' => Earnings::STATUS_RECEIVED, 'is_counted_in_stats' => 1])
            ->andWhere(['>=', 'date_time', $previousStart])
            ->andWhere(['<=', 'date_time', $previousEnd])
            ->sum('amount') ?: 0;
        
        // Расходы за предыдущие 30 дней
        $previousPeriodExpenses = Expenses::findByUser()
            ->andWhere(['status' => Expenses::STATUS_RECEIVED, 'is_counted_in_stats' => 1])
            ->andWhere(['>=', 'date_time', $previousStart])
            ->andWhere(['<=', 'date_time', $previousEnd])
            ->sum('amount') ?: 0;
        
        // Изменения в процентах для доходов и расходов (сравнение с предыдущим периодом)
        $earningsChange = 0;
        $earningsChangePercent = 0;
        $expensesChange = 0;
        $expensesChangePercent = 0;
        
        if ($previousPeriodEarnings > 0) {
            $earningsChange = $periodEarnings - $previousPeriodEarnings;
            $earningsChangePercent = ($earningsChange / $previousPeriodEarnings) * 100;
        } elseif ($periodEarnings > 0) {
            $earningsChange = $periodEarnings;
            $earningsChangePercent = 100;
        }
        
        if ($previousPeriodExpenses > 0) {
            $expensesChange = $periodExpenses - $previousPeriodExpenses;
            $expensesChangePercent = ($expensesChange / $previousPeriodExpenses) * 100;
        } elseif ($periodExpenses > 0) {
            $expensesChange = $periodExpenses;
            $expensesChangePercent = 100;
        }
        
        // Изменение общего баланса за период
        // Прибыль за период = доходы - расходы
        $periodProfit = $periodEarnings - $periodExpenses;
        $totalBalanceChange = $periodProfit;
        
        // Расчет процента изменения баланса за период
        // Баланс на начало периода = текущий баланс - прибыль за период
        $balanceAtPeriodStart = $currentBalance - $periodProfit;
        $totalBalanceChangePercent = 0;
        
        if ($balanceAtPeriodStart != 0) {
            $totalBalanceChangePercent = ($totalBalanceChange / abs($balanceAtPeriodStart)) * 100;
        } elseif ($periodProfit > 0) {
            // Если баланс был 0 и появилась прибыль, это 100% рост
            $totalBalanceChangePercent = 100;
        } elseif ($periodProfit < 0) {
            // Если баланс был 0 и появился убыток, это 100% падение
            $totalBalanceChangePercent = -100;
        }
        
        return [
            'periodEarnings' => $periodEarnings,
            'periodExpenses' => $periodExpenses,
            'earningsChange' => $earningsChange,
            'expensesChange' => $expensesChange,
            'earningsChangePercent' => $earningsChangePercent,
            'expensesChangePercent' => $expensesChangePercent,
            'totalBalanceChange' => $totalBalanceChange,
            'totalBalanceChangePercent' => $totalBalanceChangePercent,
        ];
    }
    
    /**
     * Главная страница
     *
     * @return string
     */
    public function actionIndex()
    {
        $accounts = [];
        $totalBalance = 0;
        $periodEarnings = 0;
        $periodExpenses = 0;
        $earningsChange = 0;
        $expensesChange = 0;
        $earningsChangePercent = 0;
        $expensesChangePercent = 0;
        $totalBalanceChange = 0;
        $totalBalanceChangePercent = 0;
        
        if (!Yii::$app->user->isGuest) {
            $accounts = Account::findByUser()
                ->with('currency')
                ->orderBy(['is_default' => SORT_DESC, 'name' => SORT_ASC])
                ->all();
            
            foreach ($accounts as $account) {
                $totalBalance += $account->balance;
            }
            
            // Получаем данные за последние 30 дней (передаем текущий баланс для расчета процента)
            $periodData = $this->getLast30DaysData($totalBalance);
            $periodEarnings = $periodData['periodEarnings'];
            $periodExpenses = $periodData['periodExpenses'];
            $earningsChange = $periodData['earningsChange'];
            $expensesChange = $periodData['expensesChange'];
            $earningsChangePercent = $periodData['earningsChangePercent'];
            $expensesChangePercent = $periodData['expensesChangePercent'];
            $totalBalanceChange = $periodData['totalBalanceChange'];
            $totalBalanceChangePercent = $periodData['totalBalanceChangePercent'];
        }
        
        // Определяем валюту по умолчанию
        $defaultCurrency = null;
        if (!empty($accounts)) {
            foreach ($accounts as $account) {
                if ($account->currency) {
                    $defaultCurrency = $account->currency;
                    break;
                }
            }
        }
        if (!$defaultCurrency) {
            $defaultCurrency = \app\models\Currency\Currency::findByCode('RUB');
        }
        
        return $this->render('index', [
            'accounts' => $accounts,
            'totalBalance' => $totalBalance,
            'periodEarnings' => $periodEarnings,
            'periodExpenses' => $periodExpenses,
            'earningsChange' => $earningsChange,
            'expensesChange' => $expensesChange,
            'earningsChangePercent' => $earningsChangePercent,
            'expensesChangePercent' => $expensesChangePercent,
            'totalBalanceChange' => $totalBalanceChange,
            'totalBalanceChangePercent' => $totalBalanceChangePercent,
            'defaultCurrency' => $defaultCurrency,
        ]);
    }

    /**
     * Страница логина
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Регистрация пользователя
     *
     * @return Response|string
     */
    public function actionSignup()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new SignupForm();
        if ($model->load(Yii::$app->request->post())) {
            if ($user = $model->signup()) {
                if (Yii::$app->getUser()->login($user)) {
                    Yii::$app->session->setFlash('success', 'Регистрация прошла успешно! Добро пожаловать!');
                    return $this->goHome();
                }
            } else {
                Yii::$app->session->setFlash('error', 'Ошибка при регистрации. Пожалуйста, проверьте введенные данные.');
            }
        }

        return $this->render('signup', [
            'model' => $model,
        ]);
    }

    /**
     * Выход из системы
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Страница контактов
     *
     * @return Response|string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Страница "О нас"
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }

    /**
     * Страница профиля (заглушка)
     *
     * @return Response|string
     */
    public function actionProfile()
    {
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['login']);
        }
        
        $this->title = 'Профиль';
        return $this->render('profile');
    }

    /**
     * Страница настроек (заглушка)
     *
     * @return Response|string
     */
    public function actionSettings()
    {
        if (Yii::$app->user->isGuest) {
            return $this->redirect(['login']);
        }
        
        $this->title = 'Настройки';
        return $this->render('settings');
    }
    
}
