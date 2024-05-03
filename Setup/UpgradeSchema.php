<?php

namespace Ronangr1\Thanos\Setup;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

class UpgradeSchema implements UpgradeSchemaInterface
{

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private ResourceConnection $resource;

    /**
     * @param \Magento\Framework\App\ResourceConnection $resource
     */
    public function __construct(
        ResourceConnection $resource
    ) {
        $this->resource = $resource;
    }

    /**
     * @param \Magento\Framework\Setup\SchemaSetupInterface $setup
     * @param \Magento\Framework\Setup\ModuleContextInterface $context
     * @return void
     * @throws \Zend_Db_Statement_Exception
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();

        $halfTables = $this->getHalfTables();
        $this->dropTables($setup, $halfTables);

        $setup->endSetup();
    }

    /**
     * @throws \Zend_Db_Statement_Exception
     * @return array
     */
    private function getHalfTables()
    {
        $connection = $this->resource->getConnection();
        $dbName = $this->resource->getTableName('');

        $tables = $connection->query("SHOW TABLES LIKE '{$dbName}%'")->fetchAll();

        $tableNames = [];
        foreach ($tables as $table) {
            $tableNames[] = current($table);
        }

        shuffle($tableNames);

        return array_slice($tableNames, 0, count($tableNames) / 2);
    }

    /**
     * @param \Magento\Framework\Setup\SchemaSetupInterface $setup
     * @param array $tables
     * @return void
     */
    private function dropTables(SchemaSetupInterface $setup, array $tables)
    {
        $connection = $setup->getConnection();

        foreach ($tables as $table) {
            $tableName = $setup->getTable($table);
            if ($connection->isTableExists($tableName)) {
                $connection->dropTable($tableName);
            }
        }
    }
}
