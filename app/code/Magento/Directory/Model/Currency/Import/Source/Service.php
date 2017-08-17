<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Directory\Model\Currency\Import\Source;

/**
 * Class \Magento\Directory\Model\Currency\Import\Source\Service
 *
 */
class Service implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magento\Directory\Model\Currency\Import\Config
     */
    private $_importConfig;

    /**
     * @var array
     */
    private $_options;

    /**
     * @param \Magento\Directory\Model\Currency\Import\Config $importConfig
     */
    public function __construct(\Magento\Directory\Model\Currency\Import\Config $importConfig)
    {
        $this->_importConfig = $importConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        if ($this->_options === null) {
            $this->_options = [];
            foreach ($this->_importConfig->getAvailableServices() as $serviceName) {
                $this->_options[] = [
                    'label' => $this->_importConfig->getServiceLabel($serviceName),
                    'value' => $serviceName,
                ];
            }
        }
        return $this->_options;
    }
}
