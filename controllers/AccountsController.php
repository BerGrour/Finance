<?php

namespace app\controllers;

use Yii;
use app\models\Account\Account;
use app\models\Account\AccountSearch;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use yii\web\Response;

/**
 * Контроллер для управления счетами/кошельками
 */
class AccountsController extends Controller
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
                    'recalculate' => ['POST'],
                ],
            ],
        ];
    }

    /**
     * Список всех счетов текущего пользователя
     *
     * @return string
     */
    public function actionIndex()
    {
        $searchModel = new AccountSearch();
        $dataProvider = $searchModel->search(Yii::$app->request->queryParams);

        return $this->render('index', [
            'searchModel' => $searchModel,
            'dataProvider' => $dataProvider,
        ]);
    }

    /**
     * Просмотр детальной информации о счете
     *
     * @param int $id
     * @return string
     * @throws NotFoundHttpException если счет не найден
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
     * Создание нового счета
     *
     * @return string|Response
     */
    public function actionCreate()
    {
        $model = new Account();
        $model->user_id = Yii::$app->user->id;
        $model->balance = 0.00;
        $model->initial_balance = 0.00;

        if ($model->load(Yii::$app->request->post())) {
            if ($model->isNewRecord) {
                $model->balance = $model->initial_balance ?? 0.00;
            }

            if ($model->save()) {
                Yii::$app->session->setFlash('success', 'Счет успешно создан.');
                return $this->redirect(['view', 'id' => $model->id]);
            } else {
                Yii::error($model->errors);
            }
        }

        return $this->render('create', [
            'model' => $model,
        ]);
    }

    /**
     * Обновление счета
     *
     * @param int $id
     * @return string|Response
     * @throws NotFoundHttpException если счет не найден
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $this->checkUserAccess($model);

        if ($model->load(Yii::$app->request->post()) && $model->save()) {
            Yii::$app->session->setFlash('success', 'Счет успешно обновлен.');
            return $this->redirect(['view', 'id' => $model->id]);
        }

        return $this->render('update', [
            'model' => $model,
        ]);
    }

    /**
     * Удаление счета (soft delete)
     *
     * @param int $id
     * @return Response
     * @throws NotFoundHttpException если счет не найден
     */
    public function actionDelete($id)
    {
        $model = $this->findModel($id);
        $this->checkUserAccess($model);

        if ($model->softDelete()) {
            Yii::$app->session->setFlash('success', 'Счет успешно удален.');
        } else {
            Yii::$app->session->setFlash('error', 'Ошибка при удалении счета.');
        }

        return $this->redirect(['index']);
    }

    /**
     * Восстановление удаленного счета
     *
     * @param int $id
     * @return Response
     * @throws NotFoundHttpException если счет не найден
     */
    public function actionRestore($id)
    {
        $model = Account::findDeleted()
            ->andWhere(['id' => $id])
            ->one();

        $this->checkUserAccess($model);

        if ($model === null) {
            throw new NotFoundHttpException('Счет не найден.');
        }

        if ($model->restore()) {
            Yii::$app->session->setFlash('success', 'Счет успешно восстановлен.');
        } else {
            Yii::$app->session->setFlash('error', 'Ошибка при восстановлении счета.');
        }

        return $this->redirect(['index']);
    }

    /**
     * Ручной пересчет баланса счета
     *
     * @param int $id
     * @return Response
     * @throws NotFoundHttpException если счет не найден
     */
    public function actionRecalculate($id)
    {
        $model = $this->findModel($id);
        $this->checkUserAccess($model);

        if ($model->recalculateBalance()) {
            Yii::$app->session->setFlash('success', 'Баланс счета успешно пересчитан.');
        } else {
            Yii::$app->session->setFlash('error', 'Ошибка при пересчете баланса счета.');
        }

        return $this->redirect(['view', 'id' => $model->id]);
    }

    /**
     * Поиск модели по ID
     *
     * @param int $id
     * @return Account
     * @throws NotFoundHttpException если модель не найдена
     */
    protected function findModel($id)
    {
        $model = Account::findOne($id);
        if ($model === null) {
            throw new NotFoundHttpException('Счет не найден.');
        }
        return $model;
    }

    /**
     * Проверка доступа пользователя к счету
     *
     * @param Account $model
     * @throws NotFoundHttpException если доступ запрещен
     */
    protected function checkUserAccess($model)
    {
        if ($model->user_id !== Yii::$app->user->id) {
            throw new NotFoundHttpException('Доступ запрещен.');
        }
    }
}
