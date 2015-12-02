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
     * @var array список удаленных таблиц
     * Используется в процессе уделения таблиц
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
            if (!isset($this->_deleted[$table])) {
                $this->dropTable($table);
            }
        }
    }

    /**
     * Праверка зависимостей для каректной установки FOREIGN KEY
     * @param array|string $tables
     */
    protected function dependency($tables)
    {
        foreach ((array) $tables as $table) {
            if (!isset($this->_installed[$table])) {
                $this->_installed[$table] = true;
                $this->$table();
            }
        }
    }

    /**
     * @inheritdoc
     */
    public function addForeignKey($table, $columns, $refTable, $refColumns, $delete = null, $update = null)
    {
        $this->dependency([$refTable]);
        parent::addForeignKey($table, $columns, $refTable, $refColumns, $delete, $update);
    }

    /**
     * @inheritdoc
     */
    public function dropTable($table)
    {
        if (!isset($this->_deleted[$table])) {
            if (isset($this->getForeignTables()[$table])) {
                foreach ($this->getForeignTables()[$table] as $foreign_table) {
                    $this->dropTable($foreign_table);
                }
                parent::dropTable($table);
            }
            $this->_deleted[$table] = true;
        }
    }

    /**
     * @return array
     */
    public function getForeignTables()
    {
        if ($this->_foreign_tables === null) {

            $tables = [];
            $schema = $this->db->getSchema();

            foreach ($this->getTables() as $name) {
                if (($table = $schema->getTableSchema($name, true)) !== null) {
                    if (!isset($tables[$table->fullName])) {
                        $tables[$table->fullName] = [];
                    }
                    if (empty($table->foreignKeys)) {
                        continue;
                    }
                    foreach ($table->foreignKeys as $foreign_table) {
                        if ($foreign_table[0] !== $table->fullName) {
                            $tables[$foreign_table[0]][] = $table->fullName;
                        }
                    }
                }
            }
            $this->_foreign_tables = $tables;
        }

        return $this->_foreign_tables;
    }
}