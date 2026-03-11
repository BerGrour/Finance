<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;

/**
 * Контроллер для управления долгами
 */
class DebtsController extends Controller
{
    /**
     * Главная страница раздела долгов
     *
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }
}
