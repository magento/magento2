<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesSequence\Setup;

use Magento\Framework\Setup\UpgradeDataInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;

/**
 * Recurring data upgrade for SalesSequence module.
 */
class UpgradeData implements UpgradeDataInterface
{
    /**
     * @var SequenceCreator
     */
    private $sequenceCreator;

    /**
     * @param SequenceCreator $sequenceCreator
     */
    public function __construct(
        SequenceCreator $sequenceCreator
    ) {
        $this->sequenceCreator = $sequenceCreator;
    }

    /**
     * {@inheritdoc}
     */
    public function upgrade(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->sequenceCreator->create();
    }
}
