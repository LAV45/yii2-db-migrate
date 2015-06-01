<?php
/**
 * @link https://github.com/LAV45/yii2-db-migrate
 * @copyright Copyright (c) 2015 LAV45!
 * @author Alexey Loban <lav451@gmail.com>
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace lav45\db;

use yii\db\Migration;

/**
 * Class BaseMigration
 * @package lav45\db
 */
class BaseMigration extends Migration
{
    /**
     * @inheritdoc
     */
    public function addForeignKey($table, $columns, $refTable, $refColumns, $delete = null, $update = null)
    {
        $name = $table . '_' . (is_array($columns) ? implode('_', $columns) : $columns) . '_fkey';
        parent::addForeignKey($name, $table, $columns, $refTable, $refColumns, $delete, $update);
    }

    /**
     * @inheritdoc
     */
    public function addPrimaryKey($table, $columns)
    {
        $name = $table . '_pk';
        parent::addPrimaryKey($name, $table, $columns);
    }

    /**
     * @inheritdoc
     */
    public function createIndex($table, $columns, $unique = false)
    {
        $name = $table . '_' . (is_array($columns) ? implode('_', $columns) : $columns) . '_idx';
        parent::createIndex($name, $table, $columns, $unique);
    }

    /**
     * @inheritdoc
     */
    public function resetSequence($table, $id)
    {
        $this->db->createCommand($this->db->queryBuilder->resetSequence($table, $id))->execute();
    }
} 