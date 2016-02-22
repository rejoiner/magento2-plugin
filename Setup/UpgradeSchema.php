<?php
/**
 * Copyright © 2016 Rejoiner. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Rejoiner\Acr\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;

/**
 * @codeCoverageIgnore
 */
class UpgradeSchema implements UpgradeSchemaInterface
{

    public function upgrade(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $version = $context->getVersion();
        if (version_compare($version, '1.0.0', '<=')) {
            $installer = $setup;
            $installer->startSetup();

            $table = $installer->getConnection()->newTable(
                $installer->getTable('rejoiner_acr_successful_orders')
            )->addColumn(
                'entity_id',
                Table::TYPE_INTEGER,
                null,
                ['identity' => true, 'nullable' => false, 'primary' => true],
                'Record Id'
            )->addColumn(
                'order_id',
                Table::TYPE_INTEGER,
                null,
                ['nullable' => false],
                'Order Id'
            )->addColumn(
                'created_at',
                Table::TYPE_DATETIME,
                null,
                ['nullable' => false],
                'Created at'
            )->addColumn(
                'sent_at',
                Table::TYPE_DATETIME,
                null,
                [],
                'Information sent to Rejoiner service at'
            )->addColumn(
                'response_code',
                Table::TYPE_INTEGER,
                null,
                [],
                'Response Code'
            )->setComment(
                'Backend Tracking'
            );
            $installer->getConnection()->createTable($table);
            $installer->endSetup();
        }
    }
}
