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
 */
abstract Class Migration extends \yii\db\Migration
{
    /**
     * @var array Списак таблиц которые необхадимо установить
     * Имя функции и итя таблицы должны совпадать
     */
    abstract public function getTables();

    /**
     * @var array список установленных таблиц
     * Используется в процессе создания таблиц
     */
    private $installed = [];

    /**
     * @var array список удаленных таблиц
     * Используется в процессе уделения таблиц
     */
    private $deleted = [];

    /**
     * @var array список связанных таблиц
     */
    protected $foreign_tables;

    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->dependency($this->getTables());
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        foreach ($this->getTables() as $table) {
            if (!isset($this->deleted[$table])) {
                $this->dropTable($table);
            }
        }
    }

    /**
     * Праверка зависимостей для каректной установки FOREIGN KEY
     * @param array $tables
     */
    private function dependency($tables)
    {
        foreach ((array)$tables as $table) {
            if (!isset($this->installed[$table])) {
                $this->installed[$table] = true;
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
        $name = $table . '_' . (is_array($columns) ? implode('_', $columns) : $columns) . '_fkey';
        parent::addForeignKey($name, $table, $columns, $refTable, $refColumns, $delete, $update);
    }

    /**
     * @inheritdoc
     */
    public function dropTable($table)
    {
        if (!isset($this->deleted[$table])) {
            if (isset($this->getForeignTables()[$table])) {
                foreach ($this->getForeignTables()[$table] as $foreign_table) {
                    $this->dropTable($foreign_table);
                }
                parent::dropTable($table);
            }
            $this->deleted[$table] = true;
        }
    }

    public function getForeignTables()
    {
        if ($this->foreign_tables === null) {

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
            $this->foreign_tables = $tables;
        }

        return $this->foreign_tables;
    }

    public function addPrimaryKey($table, $columns)
    {
        $name = $table . '_pk';
        parent::addPrimaryKey($name, $table, $columns);
    }

    public function createIndex($table, $columns, $unique = false)
    {
        $name = $table . '_' . (is_array($columns) ? implode('_', $columns) : $columns) . '_idx';
        parent::createIndex($name, $table, $columns, $unique);
    }

    public function resetSequence($table, $id)
    {
        $this->db->createCommand($this->db->queryBuilder->resetSequence($table, $id))->execute();
    }
}