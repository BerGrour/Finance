<?php

namespace app\models\Account;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;

/**
 * Модель поиска для Account
 */
class AccountSearch extends Account
{
    /**
     * {@inheritdoc}
     */
    public function rules(): array
    {
        return [
            [['id', 'user_id', 'currency_id', 'created_at', 'updated_at', 'deleted_at'], 'integer'],
            [['name', 'type', 'comment'], 'safe'],
            [['balance'], 'number'],
            [['is_deleted', 'is_default'], 'boolean'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        return Model::scenarios();
    }

    /**
     * Создание провайдера данных для поиска
     *
     * @param array $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Account::find()->with('currency');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'is_default' => SORT_DESC,
                    'name' => SORT_ASC,
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

        $query->andWhere(['user_id' => Yii::$app->user->id]);

        $query->andFilterWhere([
            'id' => $this->id,
            'type' => $this->type,
            'currency_id' => $this->currency_id,
            'balance' => $this->balance,
            'is_deleted' => $this->is_deleted,
            'is_default' => $this->is_default,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'name', $this->name])
            ->andFilterWhere(['like', 'comment', $this->comment]);

        return $dataProvider;
    }
}
