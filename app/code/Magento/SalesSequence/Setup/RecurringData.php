<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesSequence\Setup;

use Magento\Framework\Setup\InstallDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Recurring data upgrade for SalesSequence module.
 */
class RecurringData implements InstallDataInterface
{
    /**
     * @param SequenceCreator $sequenceCreator
     */
    public function __construct(
        private readonly SequenceCreator $sequenceCreator
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->sequenceCreator->create();
    }
}
