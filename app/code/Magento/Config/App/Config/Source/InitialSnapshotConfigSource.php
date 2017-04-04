<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\App\Config\Source;

use Magento\Framework\App\Config\ConfigSourceInterface;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\FlagManager;

/**
 * The source with previously imported configuration.
 */
class InitialSnapshotConfigSource implements ConfigSourceInterface
{
    /**
     * The factory of Flag instances.
     *
     * @var FlagManager
     */
    private $flagManager;

    /**
     * The factory of DataObject instances.
     *
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @param FlagManager $flagManager The factory of Flag instances
     * @param DataObjectFactory $dataObjectFactory The factory of DataObject instances
     */
    public function __construct(FlagManager $flagManager, DataObjectFactory $dataObjectFactory)
    {
        $this->flagManager = $flagManager;
        $this->dataObjectFactory = $dataObjectFactory;
    }

    /**
     * Retrieves previously imported configuration.
     * Snapshots are stored in flags.
     *
     * {@inheritdoc}
     */
    public function get($path = '')
    {
        $flagData = (array)($this->flagManager->getFlagData('system_config_snapshot') ?: []);

        $data = $this->dataObjectFactory->create(
            ['data' => $flagData]
        );

        return $data->getData($path) ?: [];
    }
}
