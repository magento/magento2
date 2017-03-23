<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\App\Config\Source;

use Magento\Framework\App\Config\ConfigSourceInterface;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\FlagFactory;

/**
 * The source with previously imported runtime configuration.
 */
class RuntimeSnapshotConfigSource implements ConfigSourceInterface
{
    /**
     * The factory of Flag instances.
     *
     * @var FlagFactory
     */
    private $flagFactory;

    /**
     * The factory of DataObject instances.
     *
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @param FlagFactory $flagFactory The factory of Flag instances
     * @param DataObjectFactory $dataObjectFactory The factory of DataObject instances
     */
    public function __construct(FlagFactory $flagFactory, DataObjectFactory $dataObjectFactory)
    {
        $this->flagFactory = $flagFactory;
        $this->dataObjectFactory = $dataObjectFactory;
    }

    /**
     * Retrieves snapshot of previously imported configuration.
     * Snapshots are stored in flags.
     *
     * {@inheritdoc}
     */
    public function get($path = '')
    {
        $flag = $this->flagFactory->create();
        $flag->getResource()->load($flag, 'system_config_snapshot', 'flag_code');

        $data = $this->dataObjectFactory->create(
            ['data' => (array)($flag->getFlagData() ?: [])]
        );

        return $data->getData($path) ?: [];
    }
}
