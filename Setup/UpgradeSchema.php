<?php

namespace Fastly\Cdn\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UpgradeSchemaInterface;

class UpgradeSchema implements UpgradeSchemaInterface // @codingStandardsIgnoreLine - currently best way to resolve this
{

    /**
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws \Zend_Db_Exception
     */
    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        if (version_compare($context->getVersion(), '1.0.11', '<=')) {
            $this->createFastlyStatisticsTable($installer);
        }

        if (version_compare($context->getVersion(), '1.0.12', '<=')) {
            $this->createModlyManifestTable($installer);
        }

        $installer->endSetup();
    }

    /**
     * @param SchemaSetupInterface $installer
     * @throws \Zend_Db_Exception
     */
    public function createFastlyStatisticsTable(
        SchemaSetupInterface $installer
    ) {
        $connection = $installer->getConnection();
        $tableName = $installer->getTable('fastly_statistics');

        if ($installer->getConnection()->isTableExists($tableName) != true) {
            /**
             * Create table 'fastly_statistics'
             */
            $table = $connection->newTable(
                $installer->getTable('fastly_statistics')
            )->addColumn(
                'stat_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'unsigned' => true, 'nullable' => false, 'primary' => true],
                'Stat id'
            )->addColumn(
                'action',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                30,
                ['nullable' => false],
                'Fastly action'
            )->addColumn(
                'sent',
                \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false, 'default' => 0],
                '1 = Curl req. sent | 0 = Curl req. not sent'
            )->addColumn(
                'state',
                \Magento\Framework\DB\Ddl\Table::TYPE_BOOLEAN,
                null,
                ['nullable' => false, 'default' => 0],
                '1 = configured | 0 = not_configured'
            )->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                null,
                [],
                'Action date'
            );

            $connection->createTable($table);
        }
    }

    /**
     * @param SchemaSetupInterface $installer
     * @throws \Zend_Db_Exception
     */
    public function createModlyManifestTable(
        SchemaSetupInterface $installer
    ) {
        $connection = $installer->getConnection();
        $tableName = $installer->getTable('fastly_modly_manifests');

        if ($installer->getConnection()->isTableExists($tableName) != true) {
            /**
             * Create table 'fastly_modly_manifests'
             */
            $table = $connection->newTable(
                $installer->getTable('fastly_modly_manifests')
            )->addColumn(
                'manifest_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                255,
                ['identity' => false, 'nullable' => false, 'primary' => true],
                'Manifest id'
            )->addColumn(
                'manifest_name',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                64,
                ['nullable' => false],
                'Manifest name'
            )->addColumn(
                'manifest_version',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                12,
                ['nullable' => false],
                'Manifest version'
            )->addColumn(
                'manifest_content',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                \Magento\Framework\DB\Ddl\Table::DEFAULT_TEXT_SIZE,
                ['nullable' => false],
                'Manifest content'
            );

            $connection->createTable($table);
        }
    }
}
