<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
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
    public function install(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->sequenceCreator->create();
    }
}
