<?php

namespace app\controllers;

use Yii;
use app\models\Earnings\EarningsSource;
use app\models\Earnings\EarningsSourceSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\Response;

/**
 * Контроллер для управления источниками заработка
 */
class EarningsSourceController extends Controller
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
                    'restore' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Список всех источников заработка текущего пользователя
     *
     * @return string
     */
    public function actionIndex(): string
    {
        $searchModel = new EarningsSourceSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Просмотр детальной информации об источнике заработка
     *
     * @param int $id
     * @return string
     * @throws NotFoundHttpException если источник не найден
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
     * Создание нового источника заработка
     *
     * @return string|Response
     */
    public function actionCreate()
    {
        $model = new EarningsSource();
        $model->user_id = Yii::$app->user->id;

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Источник заработка успешно создан.');
            return $this->redirect(['view', 'id' => $model->id]);
        } else {
            if ($model->hasErrors()) {
                Yii::error($model->errors);
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Обновление источника заработка
     *
     * @param int $id
     * @return string|Response
     * @throws NotFoundHttpException если источник не найден
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $this->checkUserAccess($model);

        if ($model->isStatic()) {
            Yii::$app->session->setFlash('error', 'Системный источник нельзя редактировать.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Источник заработка успешно обновлен.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Удаление источника заработка (soft delete)
     *
     * @param int $id
     * @return Response
     * @throws NotFoundHttpException если источник не найден
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $this->checkUserAccess($model);

        if ($model->isStatic()) {
            Yii::$app->session->setFlash('error', 'Системный источник нельзя удалить.');
            return $this->redirect(['index']);
        }

        if ($model->softDelete()) {
            Yii::$app->session->setFlash('success', 'Источник заработка успешно удален.');
        } else {
            Yii::$app->session->setFlash('error', 'Ошибка при удалении источника заработка.');
        }

        return $this->redirect(['index']);
    }

    /**
     * Восстановление удаленного источника заработка
     *
     * @param int $id
     * @return Response
     * @throws NotFoundHttpException если источник не найден
     */
    public function actionRestore($id)
    {
        $model = EarningsSource::findDeleted()
            ->andWhere(['id' => $id])
            ->one();

        $this->checkUserAccess($model);

        if ($model === null) {
            throw new NotFoundHttpException('Источник заработка не найден.');
        }

        if ($model->restore()) {
            Yii::$app->session->setFlash('success', 'Источник заработка успешно восстановлен.');
        } else {
            Yii::$app->session->setFlash('error', 'Ошибка при восстановлении источника заработка.');
        }

        return $this->redirect(['index']);
    }

    /**
     * Поиск модели по ID
     *
     * @param int $id
     * @return EarningsSource
     * @throws NotFoundHttpException если модель не найдена
     */
    protected function findModel($id)
    {
        $model = EarningsSource::findOne($id);
        if ($model === null) {
            throw new NotFoundHttpException('Источник заработка не найден.');
        }
        return $model;
    }

    /**
     * Проверка доступа пользователя к источнику заработка
     *
     * @param EarningsSource $model
     * @throws NotFoundHttpException если доступ запрещен
     */
    protected function checkUserAccess($model)
    {
        if ($model->user_id !== Yii::$app->user->id) {
            throw new NotFoundHttpException('Доступ запрещен.');
        }
    }
}
