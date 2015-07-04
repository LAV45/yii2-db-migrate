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
     * @param string $table
     * @param string|array $columns
     * @return string
     */
    protected function getNameForeignKey($table, $columns)
    {
        return $table . '_' . (is_array($columns) ? implode('_', $columns) : $columns) . '_fkey';
    }

    /**
     * @inheritdoc
     */
    public function addForeignKey($table, $columns, $refTable, $refColumns, $delete = null, $update = null)
    {
        $name = $this->getNameForeignKey($table, $columns);
        parent::addForeignKey($name, $table, $columns, $refTable, $refColumns, $delete, $update);
    }

    /**
     * @param string $table
     * @param string|array $columns
     * @return string
     */
    public function dropForeignKey($table, $columns)
    {
        $name = $this->getNameForeignKey($table, $columns);
        parent::dropForeignKey($name, $table);
    }

    /**
     * @param string $table
     * @return string
     */
    protected function getNamePrimaryKey($table)
    {
        return $table . '_pk';
    }

    /**
     * @inheritdoc
     */
    public function addPrimaryKey($table, $columns)
    {
        $name = $this->getNamePrimaryKey($table);
        parent::addPrimaryKey($name, $table, $columns);
    }

    /**
     * @inheritdoc
     */
    public function dropPrimaryKey($table)
    {
        $name = $this->getNamePrimaryKey($table);
        parent::dropPrimaryKey($name, $table);
    }

    /**
     * @param string $table
     * @param string|array $columns
     * @return string
     */
    protected function getNameIndex($table, $columns)
    {
        return $table . '_' . (is_array($columns) ? implode('_', $columns) : $columns) . '_idx';
    }

    /**
     * @inheritdoc
     */
    public function createIndex($table, $columns, $unique = false)
    {
        $name = $this->getNameIndex($table, $columns);
        parent::createIndex($name, $table, $columns, $unique);
    }

    /**
     * @param string $table
     * @param string|array $columns
     * @return string
     */
    public function dropIndex($table, $columns)
    {
        $name = $this->getNameIndex($table, $columns);
        parent::dropIndex($name, $table);
    }

    /**
     * @see \yii\db\QueryBuilder::resetSequence()
     */
    public function resetSequence($table, $value = null)
    {
        $this->db->createCommand($this->db->queryBuilder->resetSequence($table, $value))->execute();
    }
} 