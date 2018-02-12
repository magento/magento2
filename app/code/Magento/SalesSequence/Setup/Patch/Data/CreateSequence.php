<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SalesSequence\Setup\Patch\Data;

use Magento\Framework\App\ResourceConnection;
use Magento\SalesSequence\Setup\SequenceCreator;
use Magento\Setup\Model\Patch\DataPatchInterface;
use Magento\Setup\Model\Patch\PatchVersionInterface;

/**
 * Class CreateSequence
 * @package Magento\SalesSequence\Setup\Patch
 */
class CreateSequence implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var \Magento\Framework\Setup\ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var SequenceCreator
     */
    private $sequenceCreator;

    /**
     * CreateSequence constructor.
     * @param \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup
     * @param SequenceCreator $sequenceCreator
     */
    public function __construct(
        \Magento\Framework\Setup\ModuleDataSetupInterface $moduleDataSetup,
        SequenceCreator $sequenceCreator
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->sequenceCreator = $sequenceCreator;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->sequenceCreator->create();
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getVersion()
    {
        return '2.0.0';
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }
}
