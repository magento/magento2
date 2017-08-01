<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tax\Model\System\Config\Source\Tax;

/**
 * Class \Magento\Tax\Model\System\Config\Source\Tax\Region
 *
 * @since 2.0.0
 */
class Region implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magento\Directory\Model\ResourceModel\Region\CollectionFactory
     * @since 2.0.0
     */
    protected $_regionsFactory;

    /**
     * @param \Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionsFactory
     * @since 2.0.0
     */
    public function __construct(\Magento\Directory\Model\ResourceModel\Region\CollectionFactory $regionsFactory)
    {
        $this->_regionsFactory = $regionsFactory;
    }

    /**
     * Return list of country's regions as array
     *
     * @param bool $noEmpty
     * @param string|array|null $country
     * @return array
     * @since 2.0.0
     */
    public function toOptionArray($noEmpty = false, $country = null)
    {
        /** @var $region \Magento\Directory\Model\ResourceModel\Region\Collection */
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
