<?php
/**
 * @link https://github.com/LAV45/yii2-db-migrate
 * @copyright Copyright (c) 2015 LAV45!
 * @author Alexey Loban <lav451@gmail.com>
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace lav45\db;

use ReflectionClass;
use ReflectionMethod;

/**
 * Class MainMigration
 * @package lav45\db
 */
class MainMigration extends Migration
{
    /**
     * @var array список связанных таблиц
     */
    private $_foreign_tables;
    /**
     * @var array список установленных таблиц
     * Используется в процессе создания таблиц
     */
    private $_installed = [];
    /**
     * @var array список удалённых таблиц
     * Используется в процессе удаления таблиц
     */
    private $_deleted = [];
    /**
     * @var string
     */
    public $methodPrefix = 'table_';

    /**
     * Список таблиц которые необходимо установить
     * Имя метода в классе и имя таблицы должны совпадать
     * @return array
     */
    public function getTables()
    {
        $tables = [];
        $len = strlen($this->methodPrefix);
        $methods = (new ReflectionClass($this))
            ->getMethods(ReflectionMethod::IS_PUBLIC | ReflectionMethod::IS_PROTECTED);

        foreach ($methods as $method) {
            if (substr($method->name, 0, $len) == $this->methodPrefix) {
                $tables[] = substr($method->name, $len);
            }
        }

        return $tables;
    }

    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $this->dependency($this->getTables());
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        foreach ($this->getTables() as $table) {
            if (empty($this->_deleted[$table])) {
                $this->dropTable($table);
            }
        }
    }

    /**
     * Проверка зависимостей для корректной установки FOREIGN KEY
     * @param array|string $tables
     */
    protected function dependency($tables)
    {
        foreach ($tables as $table) {
            $table = $this->methodPrefix . $table;
            if (empty($this->_installed[$table])) {
                $this->_installed[$table] = true;
                $this->$table();
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function addForeignKey($table, $columns, $refTable, $refColumns, $delete = 'CASCADE', $update = 'CASCADE', $name = null)
    {
        $this->dependency([$refTable]);
        parent::addForeignKey($table, $columns, $refTable, $refColumns, $delete, $update, $name);
    }

    /**
     * @inheritdoc
     */
    public function dropTable($table)
    {
        if (empty($this->_deleted[$table])) {
            $foreign_tables = $this->getForeignTables($table);
            if (is_array($foreign_tables)) {
                foreach ($foreign_tables as $foreign_table) {
                    $this->dropTable($foreign_table);
                }
                parent::dropTable($table);
            }
            $this->_deleted[$table] = true;
        }
    }

    /**
     * @param string $name
     * @return array|null
     */
    public function getForeignTables($name)
    {
        if ($this->_foreign_tables === null) {
            $tables = [];
            foreach ($this->db->getSchema()->getTableSchemas() as $table) {
                if (!isset($tables[$table->fullName])) {
                    $tables[$table->fullName] = [];
                }
                foreach ($table->foreignKeys as $foreign_table) {
                    if ($foreign_table[0] !== $table->fullName && empty($tables[$foreign_table[0]][$table->fullName])) {
                        $tables[$foreign_table[0]][$table->fullName] = $table->fullName;
                    }
                }
            }
            $this->_foreign_tables = $tables;
        }

        if (isset($this->_foreign_tables[$name])) {
            return $this->_foreign_tables[$name];
        } else {
            return null;
        }
    }
}