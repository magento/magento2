<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Generate options for media database selection
 */
namespace Magento\MediaStorage\Model\Config\Source\Storage\Media;

use Magento\Framework\App\DeploymentConfig\Reader;
use Magento\Setup\Model\ConfigOptionsList;

class Database implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var Reader
     */
    protected $reader;

    /**
     * @param Reader $reader
     */
    public function __construct(Reader $reader)
    {
        $this->reader = $reader;
    }

    /**
     * Returns list of available resources
     *
     * @return array
     */
    public function toOptionArray()
    {
        $resourceOptions = [];
        $resourceConfig = $this->reader->getConfigData(ConfigOptionsList::KEY_RESOURCE);
        if (null !== $resourceConfig) {
            foreach (array_keys($resourceConfig) as $resourceName) {
                $resourceOptions[] = ['value' => $resourceName, 'label' => $resourceName];
            }
            sort($resourceOptions);
            reset($resourceOptions);
        }
        return $resourceOptions;
    }
}
