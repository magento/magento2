<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesSequence\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;

/**
 */
class Recurring implements InstallSchemaInterface
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
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->sequenceCreator->create();
    }
}
