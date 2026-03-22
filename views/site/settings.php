<?php

use yii\helpers\Html;
use yii\helpers\Url;
use app\models\User\UserPreferences;

/** @var \yii\web\View $this */
/** @var UserPreferences $prefs */

$this->title = 'Настройки';
$this->params['breadcrumbs'][] = $this->title;

$this->registerJsFile(
    '@web/js/pages/site/settings.js',
    ['depends' => \app\assets\AppAsset::class]
);
?>
<div class="site-settings">

    <div class="page-header">
        <h1><?= Html::encode($this->title) ?></h1>
    </div>

    <div class="row">

        <!-- Внешний вид -->
        <div class="col-12 col-lg-8 mb-4" id="settings-appearance">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">Внешний вид</h5>
                </div>
                <div class="card-body py-3">

                    <div class="settings-item settings-item--theme d-flex align-items-center justify-content-between py-2">
                        <span class="settings-item-label mb-0">Тема интерфейса</span>
                        <label class="theme-toggle-minimal mb-0" for="themeToggle">
                            <span class="visually-hidden">Переключить светлую и тёмную тему</span>
                            <input
                                type="checkbox"
                                class="settings-theme-toggle theme-toggle-minimal__input"
                                role="switch"
                                id="themeToggle"
                                data-save-url="<?= Url::to(['/site/save-theme']) ?>"
                                <?= $prefs->theme === UserPreferences::THEME_DARK ? 'checked' : '' ?>
                            >
                            <span class="theme-toggle-minimal__track" aria-hidden="true">
                                <span class="theme-toggle-minimal__thumb">
                                    <span class="theme-toggle-minimal__icon theme-toggle-minimal__icon--sun">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">
                                            <path d="M8 11a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm0 1a4 4 0 1 1 0-8 4 4 0 0 1 0 8zM8 0a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 0zm0 13a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 13zm8-5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2a.5.5 0 0 1 .5.5zM3 8a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2A.5.5 0 0 1 3 8zm10.657-5.657a.5.5 0 0 1 0 .707l-1.414 1.415a.5.5 0 1 1-.707-.708l1.414-1.414a.5.5 0 0 1 .707 0zm-9.193 9.193a.5.5 0 0 1 0 .707L3.05 13.657a.5.5 0 0 1-.707-.707l1.414-1.414a.5.5 0 0 1 .707 0zm9.193 2.121a.5.5 0 0 1-.707 0l-1.414-1.414a.5.5 0 0 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .707zM4.464 4.465a.5.5 0 0 1-.707 0L2.343 3.05a.5.5 0 1 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .708z"/>
                                        </svg>
                                    </span>
                                    <span class="theme-toggle-minimal__icon theme-toggle-minimal__icon--moon">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true">
                                            <path d="M6 .278a.768.768 0 0 1 .08.858 7.208 7.208 0 0 0-.878 3.46c0 4.021 3.278 7.277 7.318 7.277.527 0 1.04-.055 1.533-.16a.787.787 0 0 1 .81.316.733.733 0 0 1-.031.893A8.349 8.349 0 0 1 8.344 16C3.734 16 0 12.286 0 7.71 0 4.266 2.114 1.312 5.124.06A.752.752 0 0 1 6 .278z"/>
                                        </svg>
                                    </span>
                                </span>
                            </span>
                        </label>
                    </div>

                </div>
            </div>
        </div>

        <!-- Заглушки для будущих секций -->
        <!--
        <div class="col-12 col-lg-8 mb-4" id="settings-notifications">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">Уведомления</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">В разработке</p>
                </div>
            </div>
        </div>

        <div class="col-12 col-lg-8 mb-4" id="settings-language">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0">Язык и регион</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">В разработке</p>
                </div>
            </div>
        </div>
        -->

    </div>
</div>
