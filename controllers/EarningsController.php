<?php

namespace app\controllers;

use Yii;
use app\models\Earnings\Earnings;
use app\models\Earnings\EarningsSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\Response;

/**
 * Контроллер для управления заработком
 */
class EarningsController extends Controller
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
     * Список всех заработков текущего пользователя
     *
     * @return string
     */
    public function actionIndex(): string
    {
        $searchModel = new EarningsSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Просмотр детальной информации о заработке
     *
     * @param int $id
     * @return string
     * @throws NotFoundHttpException если заработок не найден
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
     * Создание нового заработка
     *
     * @return string|Response
     */
    public function actionCreate()
    {
        $model = new Earnings();
        $model->user_id = Yii::$app->user->id;
        $model->status = Earnings::STATUS_RECEIVED;

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
                Yii::$app->session->setFlash('success', 'Заработок успешно создан.');
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                if ($model->hasErrors()) {
                    Yii::error($model->errors);
                }
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
     * Обновление заработка
     *
     * @param int $id
     * @return string|Response
     * @throws NotFoundHttpException если заработок не найден
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
                Yii::$app->session->setFlash('success', 'Заработок успешно обновлен.');
                return $this->redirect(['view', 'id' => $model->id]);
            }
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Удаление заработка
     *
     * @param int $id
     * @return Response
     * @throws NotFoundHttpException если заработок не найден
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $this->checkUserAccess($model);

        if ($model->delete()) {
            Yii::$app->session->setFlash('success', 'Заработок успешно удален.');
        } else {
            Yii::$app->session->setFlash('error', 'Ошибка при удалении заработка.');
        }

        return $this->redirect(['index']);
    }

    /**
     * Поиск модели по ID
     *
     * @param int $id
     * @return Earnings
     * @throws NotFoundHttpException если модель не найдена
     */
    protected function findModel($id)
    {
        $model = Earnings::findOne($id);
        if ($model === null) {
            throw new NotFoundHttpException('Заработок не найден.');
        }
        return $model;
    }

    /**
     * Проверка доступа пользователя к заработку
     *
     * @param Earnings $model
     * @throws NotFoundHttpException если доступ запрещен
     */
    protected function checkUserAccess($model)
    {
        if ($model->user_id !== Yii::$app->user->id) {
            throw new NotFoundHttpException('Доступ запрещен.');
        }
    }
}
