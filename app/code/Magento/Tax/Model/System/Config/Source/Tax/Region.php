<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Tax\Model\System\Config\Source\Tax;

class Region implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var \Magento\Directory\Model\Resource\Region\CollectionFactory
     */
    protected $_regionsFactory;

    /**
     * @param \Magento\Directory\Model\Resource\Region\CollectionFactory $regionsFactory
     */
    public function __construct(\Magento\Directory\Model\Resource\Region\CollectionFactory $regionsFactory)
    {
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
        /** @var $region \Magento\Directory\Model\Resource\Region\Collection */
        $regionCollection = $this->_regionsFactory->create();
        $options = $regionCollection->addCountryFilter($country)->toOptionArray();

        if ($noEmpty) {
            unset($options[0]);
        } else {
            if ($options) {
                $options[0] = array('value' => '0', 'label' => '*');
            } else {
                $options = array(array('value' => '0', 'label' => '*'));
            }
        }

        return $options;
    }
}
