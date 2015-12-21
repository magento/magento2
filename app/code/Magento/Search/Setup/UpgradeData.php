<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Search\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * @codeCoverageIgnore
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * {@inheritdoc}
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        if (version_compare($context->getVersion(), '2.0.3', '<')) {

            // Update "scope_type" to "stores" for non-zero "scope_id" values
            $table = $setup->getTable('search_synonyms');
            $bind = ['scope_type' => \Magento\Store\Model\ScopeInterface::SCOPE_STORES];
            $where = ['scope_id != ?' => 0];
            $setup->getConnection()->update($table, $bind, $where);
        }
        $setup->endSetup();
    }
}

