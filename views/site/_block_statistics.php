<?php

use yii\helpers\Html;

/**
 * Частичное представление блока "Общая статистика"
 */
?>
<div class="statistics-block card shadow-sm" id="statisticsCard">
    <div class="card-header bg-primary text-white position-relative">
        <h4 class="mb-0">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" class="bi bi-bar-chart" viewBox="0 0 16 16" style="vertical-align: -0.125em; margin-right: 0.5rem;">
                <path d="M4 11a1 1 0 1 1 2 0v1a1 1 0 1 1-2 0v-1zm6-4a1 1 0 1 1 2 0v5a1 1 0 1 1-2 0V7zM7 9a1 1 0 0 1 2 0v3a1 1 0 1 1-2 0V9zm6-5a1 1 0 1 1 2 0v7a1 1 0 1 1-2 0V4z"/>
            </svg>
            Общая статистика
        </h4>
    </div>
    <div class="card-body position-relative">
        <div class="statistics-content" id="statisticsContent">
            <div class="statistics-chart-wrapper">
                <div class="chart-container" id="last30days-chart"></div>
                <div class="chart-legend" id="last30days-legend"></div>
            </div>
            <div class="row mt-4">
                <div class="col-12 text-center">
                    <?= Html::a('Подробная статистика', ['/statistics/index'], ['class' => 'btn btn-primary']) ?>
                </div>
            </div>
        </div>
    </div>
</div>
