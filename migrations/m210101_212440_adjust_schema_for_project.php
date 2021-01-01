<?php

use yii\db\Migration;

class m210101_212440_adjust_schema_for_project extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%project}}', 'uuid', $this->string(32)->notNull()->after('id'));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%project}}', 'uuid');
    }
}
