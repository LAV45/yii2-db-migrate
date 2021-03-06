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
     * @var string Default options for creating a table. Use in [[createTable]] method
     */
    public $defaultTableOptions;

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

        $result = implode('_', $attributes);

        /**
         * @see http://dev.mysql.com/doc/refman/5.5/en/identifiers.html
         * @see https://www.postgresql.org/docs/current/static/sql-syntax-lexical.html#SQL-SYNTAX-IDENTIFIERS
         */
        if (strlen($result) > 64) {
            $result = md5($result);
        }

        return $result;
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
        $tableSchema = $this->db->getSchema()->getTableSchema($table);
        $primaryKey = $tableSchema->primaryKey;
        if (count($primaryKey) > 1) {
            return;
        }
        /** @var \yii\db\ColumnSchema $primaryKeyColumn */
        $primaryKeyColumn = $tableSchema->getColumn($primaryKey[0]);
        if ($primaryKeyColumn->autoIncrement === false) {
            return;
        }

        $time = $this->beginCommand("reset sequence {$table}");
        $sql = $this->db->queryBuilder->resetSequence($table, $value);
        $this->db->createCommand($sql)->execute();
        $this->endCommand($time);
    }

    /**
     * Builds and executes a SQL statement for creating a new DB table.
     *
     * The columns in the new  table should be specified as name-definition pairs (e.g. 'name' => 'string'),
     * where name stands for a column name which will be properly quoted by the method, and definition
     * stands for the column type which can contain an abstract DB type.
     *
     * The [[QueryBuilder::getColumnType()]] method will be invoked to convert any abstract type into a physical one.
     *
     * If a column is specified with definition only (e.g. 'PRIMARY KEY (name, type)'), it will be directly
     * put into the generated SQL.
     *
     * @param string $table the name of the table to be created. The name will be properly quoted by the method.
     * @param array $columns the columns (name => definition) in the new table.
     * @param string $options additional SQL fragment that will be appended to the generated SQL.
     */
    public function createTable($table, $columns, $options = null)
    {
        parent::createTable($table, $columns, $options ?: $this->defaultTableOptions);
    }

    /**
     * Creates and executes an INSERT SQL statement.
     * The method will properly escape the column names, and bind the values to be inserted.
     * @param string $table the table that new rows will be inserted into.
     * @param array $columns the column data (name => value) to be inserted into the table.
     * @return array|false primary key values or false if the command fails
     */
    public function insert($table, $columns)
    {
        $time = $this->beginCommand("insert into {$table}");
        $result = $this->db->getSchema()->insert($table, $columns);
        $this->endCommand($time);
        return $result;
    }
} 
