<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Rejoiner\Acr\Setup;

use Magento\Framework\Setup\UpgradeSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

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
                $installer->getTable('rejoiner_acr_success_orders')
            )->addColumn(
                'entity_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                array('identity' => true, 'nullable' => false, 'primary' => true),
                'Record Id'
            )->addColumn(
                'order_id',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                array('nullable' => false),
                'Order Id'
            )->addColumn(
                'created_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                null,
                array('nullable' => false),
                'Created at'
            )->addColumn(
                'sent_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                null,
                array(),
                'Information sent to Rejoiner service at'
            )->addColumn(
                'response_code',
                \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                null,
                array(),
                'Response Code'
            )->setComment(
                'Backend Tracking'
            );
            $installer->getConnection()->createTable($table);



            $installer->endSetup();
        }
    }
}
