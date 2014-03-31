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
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Fedex\Model\Plugin\Rma\Block\Adminhtml\Rma\Edit\Tab\General;

/**
 * Checkout cart shipping block plugin
 */
class Shippingmethod
{
    /**
     * @var \Magento\Core\Model\Store\Config
     */
    protected $_storeConfig;

    /**
     * @param \Magento\Core\Model\Store\Config $storeConfig
     */
    public function __construct(\Magento\Core\Model\Store\Config $storeConfig)
    {
        $this->_storeConfig = $storeConfig;
    }

    /**
     * @param \Magento\Object $subject
     * @param bool $result
     * @return bool
     */
    public function afterCanDisplayCustomValue(\Magento\Object $subject, $result)
    {
        $carrierCode = $subject->getShipment()->getCarrierCode();
        if (!$carrierCode) {
            return (bool)$result || false;
        }
        return (bool)$result || (bool)$carrierCode == \Magento\Fedex\Model\Carrier::CODE;
    }
}
