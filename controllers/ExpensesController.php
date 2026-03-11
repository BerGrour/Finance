<?php

namespace app\controllers;

use Yii;
use app\models\Expenses\Expenses;
use app\models\Expenses\ExpensesSearch;
use app\models\Transfers\TransferForm;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\Response;

/**
 * Контроллер для управления тратами
 */
class ExpensesController extends Controller
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
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Список всех трат текущего пользователя
     *
     * @return string
     */
    public function actionIndex(): string
    {
        $searchModel = new ExpensesSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Просмотр детальной информации о трате
     *
     * @param int $id
     * @return string
     * @throws NotFoundHttpException если трата не найдена
     */
    public function actionView($id)
    {
        $model = $this->findModel($id);
        $this->checkUserAccess($model);
        
        return $this->render('view', [
            'model' => $model,
        ]);
    }

    /**
     * Создание новой траты
     *
     * @return string|Response
     */
    public function actionCreate()
    {
        $model = new Expenses();
        $model->user_id = Yii::$app->user->id;
        $model->status = Expenses::STATUS_RECEIVED;

        $post = Yii::$app->request->post();
        if ($model->load($post)) {
            // Обрабатываем дату и время из формы
            if (isset($post['date']) && !empty($post['date'])) {
                $time = isset($post['time']) && !empty($post['time']) ? $post['time'] : null;
                $model->setDateTime($post['date'], $time);
            } else {
                $model->addError('date_time', 'Дата обязательна для заполнения');
            }
            
            if ($model->category == Expenses::CATEGORY_TRANSFER) {
                $transfer_account_id = isset($post['transfer_account_id']) ? (int)$post['transfer_account_id'] : null;
                if (empty($transfer_account_id)) {
                    $model->addError('category', 'Необходимо выбрать счет получателя для перевода');
                }
            }
            
            if (!$model->hasErrors() && $model->save()) {
                $isTransfer = $model->category == Expenses::CATEGORY_TRANSFER;

                if ($isTransfer) {
                    $transferForm = new TransferForm();
                    $transferForm->expense = $model;
                    $transferForm->transfer_account_id = isset($post['transfer_account_id']) ? (int)$post['transfer_account_id'] : null;

                    if (!$transferForm->createTransfer()) {
                        foreach ($transferForm->getErrors() as $errors) {
                            foreach ($errors as $error) {
                                $model->addError('category', $error);
                            }
                        }

                        return $this->render('create', [
                            'model' => $model,
                        ]);
                    }
                }

                Yii::$app->session->setFlash('success', 'Трата успешно создана.');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        } else {
            // Устанавливаем данные по умолчанию для отображения в форме
            $model->date_time = time();
            $model->is_counted_in_stats = 1;
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Обновление траты
     *
     * @param int $id
     * @return string|Response
     * @throws NotFoundHttpException если трата не найдена
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $this->checkUserAccess($model);

        $post = Yii::$app->request->post();
        if ($model->load($post)) {
            // Обрабатываем дату и время из формы
            if (isset($post['date']) && !empty($post['date'])) {
                $time = isset($post['time']) && !empty($post['time']) ? $post['time'] : null;
                $model->setDateTime($post['date'], $time);
            } else {
                $model->addError('date_time', 'Дата обязательна для заполнения');
            }
            
            if (!$model->hasErrors() && $model->save()) {
                Yii::$app->session->setFlash('success', 'Трата успешно обновлена.');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Удаление траты
     *
     * @param int $id
     * @return Response
     * @throws NotFoundHttpException если трата не найдена
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $this->checkUserAccess($model);

        if ($model->delete()) {
            Yii::$app->session->setFlash('success', 'Трата успешно удалена.');
        } else {
            Yii::$app->session->setFlash('error', 'Ошибка при удалении траты.');
        }

        return $this->redirect(['index']);
    }

    /**
     * Поиск модели по ID
     *
     * @param int $id
     * @return Expenses
     * @throws NotFoundHttpException если модель не найдена
     */
    protected function findModel($id)
    {
        $model = Expenses::findOne($id);
        if ($model === null) {
            throw new NotFoundHttpException('Трата не найдена.');
        }
        return $model;
    }

    /**
     * Проверка доступа пользователя к трате
     *
     * @param Expenses $model
     * @throws NotFoundHttpException если доступ запрещен
     */
    protected function checkUserAccess($model)
    {
        if ($model->user_id !== Yii::$app->user->id) {
            throw new NotFoundHttpException('Доступ запрещен.');
        }
    }
}
