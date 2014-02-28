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
 * @category    Magento
 * @package     Magento_Shipping
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Shipping\Model\Config\Source;

class Allmethods implements \Magento\Option\ArrayInterface
{
    /**
     * Core store config
     *
     * @var \Magento\Core\Model\Store\Config
     */
    protected $_coreStoreConfig;

    /**
     * @var \Magento\Shipping\Model\Config
     */
    protected $_shippingConfig;

    /**
     * @param \Magento\Core\Model\Store\Config $coreStoreConfig
     * @param \Magento\Shipping\Model\Config $shippingConfig
     */
    public function __construct(
        \Magento\Core\Model\Store\Config $coreStoreConfig,
        \Magento\Shipping\Model\Config $shippingConfig
    ) {
        $this->_coreStoreConfig = $coreStoreConfig;
        $this->_shippingConfig = $shippingConfig;
    }

    /**
     * Return array of carriers.
     * If $isActiveOnlyFlag is set to true, will return only active carriers
     *
     * @param bool $isActiveOnlyFlag
     * @return array
     */
    public function toOptionArray($isActiveOnlyFlag=false)
    {
        $methods = array(array('value'=>'', 'label'=>''));
        $carriers = $this->_shippingConfig->getAllCarriers();
        foreach ($carriers as $carrierCode=>$carrierModel) {
            if (!$carrierModel->isActive() && (bool)$isActiveOnlyFlag === true) {
                continue;
            }
            $carrierMethods = $carrierModel->getAllowedMethods();
            if (!$carrierMethods) {
                continue;
            }
            $carrierTitle = $this->_coreStoreConfig->getConfig('carriers/'.$carrierCode.'/title');
            $methods[$carrierCode] = array(
                'label'   => $carrierTitle,
                'value' => array(),
            );
            foreach ($carrierMethods as $methodCode=>$methodTitle) {
                $methods[$carrierCode]['value'][] = array(
                    'value' => $carrierCode.'_'.$methodCode,
                    'label' => '['.$carrierCode.'] '.$methodTitle,
                );
            }
        }

        return $methods;
    }
}
