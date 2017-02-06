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
    private $foreign_tables;
    /**
     * @var array список установленных таблиц
     * Используется в процессе создания таблиц
     */
    private $installed = [];
    /**
     * @var array список удалённых таблиц
     * Используется в процессе удаления таблиц
     */
    private $deleted = [];
    /**
     * @var string
     * @since 0.4.1
     */
    public $methodPrefix = 'table_';

    /**
     * Список таблиц которые необходимо установить
     * Имя метода в классе и имя таблицы должны совпадать
     * @return array
     */
    protected function getTables()
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
            if (empty($this->deleted[$table])) {
                $this->dropTable($table);
            }
        }
    }

    /**
     * Проверка зависимостей для корректной установки FOREIGN KEY
     * @param array|string $tables
     */
    private function dependency($tables)
    {
        foreach ($tables as $table) {
            if (method_exists($this, $this->methodPrefix . $table)) {
                $table = $this->methodPrefix . $table;
            }
            if (empty($this->installed[$table])) {
                $this->installed[$table] = true;
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
        if (empty($this->deleted[$table])) {
            $foreign_tables = $this->getForeignTables($table);
            if (is_array($foreign_tables)) {
                foreach ($foreign_tables as $foreign_table) {
                    $this->dropTable($foreign_table);
                }
                parent::dropTable($table);
            }
            $this->deleted[$table] = true;
        }
    }

    /**
     * @param string $name
     * @return array|null
     */
    private function getForeignTables($name)
    {
        if ($this->foreign_tables === null) {
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
            $this->foreign_tables = $tables;
        }

        if (isset($this->foreign_tables[$name])) {
            return $this->foreign_tables[$name];
        } else {
            return null;
        }
    }
}