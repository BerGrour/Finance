<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;

/**
 * Контроллер для управления инвестициями
 */
class InvestmentsController extends Controller
{
    /**
     * Главная страница раздела инвестиций
     *
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }
}
