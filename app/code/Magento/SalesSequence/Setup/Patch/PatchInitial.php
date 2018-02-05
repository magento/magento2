<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesSequence\Setup\Patch;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;


/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class PatchInitial
{


    /**
     * @param SequenceCreator $sequenceCreator
     */
    private $sequenceCreator;

    /**
     * @param SequenceCreator $sequenceCreator
     */
    public function __construct(SequenceCreator $sequenceCreator)
    {
        $this->sequenceCreator = $sequenceCreator;
    }

    /**
     * Do Upgrade
     *
     * @param ModuleDataSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function up(ModuleDataSetupInterface $setup, ModuleContextInterface $context)
    {
        $this->sequenceCreator->create();

    }

}
