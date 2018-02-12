<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\SampleData\Setup\Patch;

use Magento\Framework\Setup;
use Magento\Framework\App\ResourceConnection;
use Magento\Setup\Model\Patch\DataPatchInterface;
use Magento\Setup\Model\Patch\PatchVersionInterface;

/**
 * Class ClearSampleDataState
 * @package Magento\SampleData\Setup\Patch
 */
class ClearSampleDataState implements DataPatchInterface, PatchVersionInterface
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var Setup\SampleData\State
     */
    private $state;

    /**
     * ClearSampleDataState constructor.
     * @param ResourceConnection $resourceConnection
     * @param Setup\SampleData\State $state
     */
    public function __construct(
        ResourceConnection $resourceConnection,
        \Magento\Framework\Setup\SampleData\State $state
    ) {
        $this->resourceConnection = $resourceConnection;
        $this->state = $state;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->state->clearState();
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
    public function getVersion()
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
