<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\AccessControl;
use app\models\Earnings\Earnings;
use app\models\Expenses\Expenses;

/**
 * Контроллер для отображения статистики
 */
class StatisticsController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }
    /**
     * Главная страница раздела статистики
     *
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * API для получения данных статистики по категории за последние 30 дней
     *
     * @param string $category Категория (earnings, expenses, investments, debts)
     * @param int $intervals Количество интервалов (по умолчанию 30 - по дням)
     * @return array Response JSON ответ с данными
     */
    public function actionGetData($category, $intervals = 30)
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        if (Yii::$app->user->isGuest) {
            return ['error' => 'Необходима авторизация'];
        }
        
        $data = [];
        
        // Последние 30 дней
        $now = time();
        $startTimestamp = mktime(0, 0, 0, date('n', $now), date('j', $now) - 30, date('Y', $now));
        $endTimestamp = $now;
        
        // Вычисляем размер интервала (каждый интервал = один день) (86400 секунд = 1 день)
        $intervalSize = 86400;
        
        // Получаем данные для каждого интервала (по дням)
        for ($i = 0; $i < $intervals; $i++) {
            $intervalStart = $startTimestamp + ($i * $intervalSize);
            $intervalEnd = $startTimestamp + (($i + 1) * $intervalSize);
            
            // Для последнего интервала используем текущее время
            if ($i == $intervals - 1) {
                $intervalEnd = $endTimestamp;
            }
            
            $sum = 0;
            
            switch ($category) {
                case 'earnings':
                    $sum = Earnings::findByUser()
                        ->andWhere(['status' => Earnings::STATUS_RECEIVED, 'is_counted_in_stats' => 1])
                        ->andWhere(['>=', 'date_time', (int)$intervalStart])
                        ->andWhere(['<', 'date_time', (int)$intervalEnd])
                        ->sum('amount') ?: 0;
                    break;
                    
                case 'expenses':
                    $sum = Expenses::findByUser()
                        ->andWhere(['status' => Expenses::STATUS_RECEIVED, 'is_counted_in_stats' => 1])
                        ->andWhere(['>=', 'date_time', (int)$intervalStart])
                        ->andWhere(['<', 'date_time', (int)$intervalEnd])
                        ->sum('amount') ?: 0;
                    break;
                    
                case 'investments':
                case 'debts':
                    // Пока возвращаем 0, можно добавить позже
                    $sum = 0;
                    break;
            }
            
            $data[] = round($sum, 2);
        }
        
        return $data;
    }
}
