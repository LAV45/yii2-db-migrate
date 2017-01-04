<?php
/**
 * @link https://github.com/LAV45/yii2-db-migrate
 * @copyright Copyright (c) 2015 LAV45!
 * @author Alexey Loban <lav451@gmail.com>
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace lav45\db;

/**
 * Class Migration
 * @package lav45\db
 */
class Migration extends \yii\db\Migration
{
    /**
     * @param string $table
     * @param null|string|string[] $columns
     * @return string
     */
    protected function normalizeName($table, $columns = null)
    {
        $attributes = empty($columns) ? [] : (array)$columns;
        array_unshift($attributes, $table);
        $attributes = array_map(function ($val) {
            $items = preg_split('~[^\w]+~', $val, null, PREG_SPLIT_NO_EMPTY);
            return implode('_', $items);
        }, $attributes);

        return implode('_', $attributes);
    }
    
    /**
     * @param string $table
     * @param string|array $columns
     * @return string
     */
    protected function getNameForeignKey($table, $columns)
    {
        return $this->normalizeName($table, $columns) . '_fkey';
    }

    /**
     * Builds a SQL statement for adding a foreign key constraint to an existing table.
     * The method will properly quote the table and column names.
     * @param string $table the table that the foreign key constraint will be added to.
     * @param string|array $columns the name of the column to that the constraint will be added on. If there are multiple columns, separate them with commas or use an array.
     * @param string $refTable the table that the foreign key references to.
     * @param string|array $refColumns the name of the column that the foreign key references to. If there are multiple columns, separate them with commas or use an array.
     * @param string $delete the ON DELETE option. Most DBMS support these options: RESTRICT, CASCADE, NO ACTION, SET DEFAULT, SET NULL
     * @param string $update the ON UPDATE option. Most DBMS support these options: RESTRICT, CASCADE, NO ACTION, SET DEFAULT, SET NULL
     * @param string $name the name of the foreign key constraint.
     */
    public function addForeignKey($table, $columns, $refTable, $refColumns, $delete = 'CASCADE', $update = 'CASCADE', $name = null)
    {
        $name = $name ?: $this->getNameForeignKey($table, $columns);
        parent::addForeignKey($name, $table, $columns, $refTable, $refColumns, $delete, $update);
    }

    /**
     * Builds a SQL statement for dropping a foreign key constraint.
     * @param string $table the table whose foreign is to be dropped. The name will be properly quoted by the method.
     * @param string|array $columns the name of the column to that the constraint will be added on. If there are multiple columns, separate them with commas or use an array.
     * @param string $name the name of the foreign key constraint to be dropped. The name will be properly quoted by the method.
     */
    public function dropForeignKey($table, $columns, $name = null)
    {
        $name = $name ?: $this->getNameForeignKey($table, $columns);
        parent::dropForeignKey($name, $table);
    }

    /**
     * @param string $table
     * @return string
     */
    protected function getNamePrimaryKey($table)
    {
        return $this->normalizeName($table) . '_pk';
    }

    /**
     * Builds and executes a SQL statement for creating a primary key.
     * The method will properly quote the table and column names.
     * @param string $table the table that the primary key constraint will be added to.
     * @param string|array $columns comma separated string or array of columns that the primary key will consist of.
     * @param string $name default null, the name of the primary key constraint.
     */
    public function addPrimaryKey($table, $columns, $name = null)
    {
        $name = $name ?: $this->getNamePrimaryKey($table);
        parent::addPrimaryKey($name, $table, $columns);
    }

    /**
     * Builds and executes a SQL statement for dropping a primary key.
     * @param string $table the table that the primary key constraint will be removed from.
     * @param string $name the name of the primary key constraint to be removed.
     */
    public function dropPrimaryKey($table, $name = null)
    {
        $name = $name ?: $this->getNamePrimaryKey($table);
        parent::dropPrimaryKey($name, $table);
    }

    /**
     * @param string $table
     * @param string|array $columns
     * @return string
     */
    protected function getNameIndex($table, $columns)
    {
        return $this->normalizeName($table, $columns) . '_idx';
    }

    /**
     * Builds and executes a SQL statement for creating a new index.
     * @param string $name the name of the index. The name will be properly quoted by the method.
     * @param string $table the table that the new index will be created for. The table name will be properly quoted by the method.
     * @param string|array $columns the column(s) that should be included in the index. If there are multiple columns, please separate them
     * by commas or use an array. Each column name will be properly quoted by the method. Quoting will be skipped for column names that
     * include a left parenthesis "(".
     * @param boolean $unique whether to add UNIQUE constraint on the created index.
     */
    public function createIndex($table, $columns, $unique = false, $name = null)
    {
        $name = $name ?: $this->getNameIndex($table, $columns);
        parent::createIndex($name, $table, $columns, $unique);
    }

    /**
     * Builds and executes a SQL statement for dropping an index.
     * @param string $table the table whose index is to be dropped. The name will be properly quoted by the method.
     * @param string|array $columns the column(s) that should be included in the index. If there are multiple columns, please separate them
     * @param string $name the name of the index to be dropped. The name will be properly quoted by the method.
     */
    public function dropIndex($table, $columns, $name = null)
    {
        $name = $name ?: $this->getNameIndex($table, $columns);
        parent::dropIndex($name, $table);
    }

    /**
     * @see \yii\db\QueryBuilder::resetSequence()
     *
     * @param string $table the name of the table whose primary key sequence will be reset
     * @param array|string $value the value for the primary key of the next new row inserted. If this is not set,
     */
    public function resetSequence($table, $value = null)
    {
        $this->db->createCommand($this->db->queryBuilder->resetSequence($table, $value))->execute();
    }
} 
