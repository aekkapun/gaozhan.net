<?php

use yii\db\Migration;

class m210101_212440_adjust_schema_for_image extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%image}}', 'ext', $this->string(32)->null()->defaultValue('')->after('project_id'));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%image}}', 'uuid');
    }
}
