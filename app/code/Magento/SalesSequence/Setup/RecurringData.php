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
 * @since 2.2.0
 */
class RecurringData implements InstallDataInterface
{
    /**
     * @var SequenceCreator
     * @since 2.2.0
     */
    private $sequenceCreator;

    /**
     * @param SequenceCreator $sequenceCreator
     * @since 2.2.0
     */
    public function __construct(
        SequenceCreator $sequenceCreator
    ) {
        $this->sequenceCreator = $sequenceCreator;
    }

    /**
     * {@inheritdoc}
     * @since 2.2.0
     */
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->sequenceCreator->create();
    }
}
