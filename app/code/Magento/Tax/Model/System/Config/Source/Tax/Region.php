<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\System\Config\Source\Tax;

use Magento\Directory\Model\ResourceModel\Region\Collection as RegionCollection;
use Magento\Directory\Model\ResourceModel\Region\CollectionFactory;
use Magento\Framework\Option\ArrayInterface;

class Region implements ArrayInterface
{
    /**
     * @var CollectionFactory
     */
    protected $_regionsFactory;

    /**
     * @param CollectionFactory $regionsFactory
     */
    public function __construct(
        CollectionFactory $regionsFactory
    ) {
        $this->_regionsFactory = $regionsFactory;
    }

    /**
     * Return list of country's regions as array
     *
     * @param bool $noEmpty
     * @param string|array|null $country
     * @return array
     */
    public function toOptionArray($noEmpty = false, $country = null)
    {
        /** @var RegionCollection $region */
        $regionCollection = $this->_regionsFactory->create();
        $options = $regionCollection->addCountryFilter($country)->toOptionArray();

        if ($noEmpty) {
            unset($options[0]);
        } else {
            if ($options) {
                $options[0] = ['value' => '0', 'label' => '*'];
            } else {
                $options = [['value' => '0', 'label' => '*']];
            }
        }

        return $options;
    }
}
