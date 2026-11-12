<?php

use yii\db\Migration;
use yii\db\Query;


/**
 * Class m201112_081459_transfer_brands
 */
class m201112_081459_transfer_brands extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function up()
    {
        // Fetch data from some tables using Query Builder
        $data = (new Query)
            ->select([
                'b.*',
            ])
            ->from('brands b');

        // See more Database Accessing methods at: http://www.yiiframework.com/doc-2.0/guide-db-migrations.html#db-accessing-methods
        foreach ($data->each(100) as $row) {

            // Insert new rows
            $this->insert('brand', [
                'id' => $row['id_brand'],
                'name' => $row['brand'],
                'created_by' => 1,
                'updated_by' => 1,
                'status' => 1,
                'deleted' => 0,
            ]);
            //Insert new rows
        }

    }

    /**
     * {@inheritdoc}
     */
    public function down()
    {
        echo "m201112_081459_transfer_brands cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m201112_081459_transfer_brands cannot be reverted.\n";

        return false;
    }
    */
}
