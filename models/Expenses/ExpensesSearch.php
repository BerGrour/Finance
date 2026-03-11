<?php
declare(strict_types=1);

namespace app\models\Expenses;

use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * Модель поиска для Expenses
 */
class ExpensesSearch extends Expenses
{
    public ?string $date_from = null;
    public ?string $date_to = null;

    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['id', 'account_id', 'category', 'status', 'is_counted_in_stats'], 'integer'],
            [['amount'], 'number'],
            [['comment', 'date_from', 'date_to'], 'safe'],
            [['date_from', 'date_to'], 'date', 'format' => 'php:d.m.Y'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios(): array
    {
        return Model::scenarios();
    }

    /**
     * @param array $params
     * @return ActiveDataProvider
     */
    public function search(array $params): ActiveDataProvider
    {
        $query = Expenses::findByUser()->with(['account']);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'date_time' => SORT_DESC,
                    'created_at' => SORT_DESC,
                ],
            ],
            'pagination' => [
                'pageSize' => 20,
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'account_id' => $this->account_id,
            'category' => $this->category,
            'status' => $this->status,
            'is_counted_in_stats' => $this->is_counted_in_stats,
        ]);

        if ($this->amount !== null && $this->amount !== '') {
            $query->andFilterWhere(['amount' => $this->amount]);
        }

        if ($this->date_from !== null && $this->date_from !== '') {
            $query->andWhere(['>=', 'date_time', strtotime($this->date_from)]);
        }

        if ($this->date_to !== null && $this->date_to !== '') {
            $query->andWhere(['<=', 'date_time', strtotime($this->date_to . ' 23:59:59')]);
        }

        $query->andFilterWhere(['like', 'comment', $this->comment]);

        return $dataProvider;
    }
}
