<?php

use yii\db\Migration;
use yii\db\Query;


/**
 * Class m201112_082221_transfer_products
 */
class m201112_082221_transfer_products extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Fetch data from some tables using Query Builder
        $data = (new Query)
            ->select([
                's.*',
            ])
            ->from('serii s');

        // See more Database Accessing methods at: http://www.yiiframework.com/doc-2.0/guide-db-migrations.html#db-accessing-methods
        foreach ($data->each(100) as $row) {

            // Insert new rows
            $this->insert('product', [
                'id' => $row['id_serie'],
                'series' => $row['serie'],
                'brand_id' => $row['id_brand'],
                'price' => $row['pret'],
                'quantity' => $row['cant'],
                'status' => 1,
                'deleted' => 0,

            ]);
            //Insert new rows
        }
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m201112_082221_transfer_products cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m201112_082221_transfer_products cannot be reverted.\n";

        return false;
    }
    */
}
