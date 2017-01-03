<?php
/**
 * @link https://github.com/LAV45/yii2-db-migrate
 * @copyright Copyright (c) 2015 LAV45!
 * @author Alexey Loban <lav451@gmail.com>
 * @license http://opensource.org/licenses/BSD-3-Clause
 */

namespace lav45\db;

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
     * Список таблиц которые необходимо установить
     * Имя метода в классе и имя таблицы должны совпадать
     * @return array
     */
    public function getTables()
    {
        return [];
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
        foreach ((array) $tables as $table) {
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
            foreach ($this->getForeignTables($table) as $foreign_table) {
                $this->dropTable($foreign_table);
            }
            parent::dropTable($table);
            $this->_deleted[$table] = true;
        }
    }

    /**
     * @param string $name
     * @return array
     */
    public function getForeignTables($name)
    {
        if ($this->_foreign_tables === null) {
            $tables = [];
            foreach ($this->db->getSchema()->getTableSchemas() as $table) {
                foreach ($table->foreignKeys as $foreign_table) {
                    if ($foreign_table[0] !== $table->fullName && empty($tables[$foreign_table[0]][$table->fullName])) {
                        $tables[$foreign_table[0]][$table->fullName] = $table->fullName;
                    }
                }
            }
            $this->_foreign_tables = $tables;
        }

        return isset($this->_foreign_tables[$name]) ? $this->_foreign_tables[$name] : [];
    }
}