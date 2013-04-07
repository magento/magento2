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
 * @category    Mage
 * @package     Mage_Shipping
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Shipping_Model_Carrier_Service_Callback implements Mage_Shipping_Model_Carrier_Service_Interface
{
    const EVENT_SHIPPING_GET_RATES = 'shipping/get_rates';

    /** @var Mage_Webhook_Helper_Data */
    protected $_dispatchHelper;


    public function __construct(Mage_Webhook_Helper_Data $dispatchHelper)
    {
        $this->_dispatchHelper = $dispatchHelper;
    }

    /**
     * @Type callback
     * @Consumes(schema="http://www.magento.com/schemas/shippingrate-input.xsd", bundle="consumer-ux")
     * @Produces(schema="http://www.magento.com/schemas/shippingrate-output.xsd", bundle="consumer-ux")
     */
    public function getRates(array $shippingRateInput)
    {
        $subscriberExtensionId = $shippingRateInput['carrierConfiguration']['subscriber'];
        $callbackOutput = $this->_dispatchHelper->dispatchCallback(
            $subscriberExtensionId,
            self::EVENT_SHIPPING_GET_RATES,
            $shippingRateInput
        );
        return $callbackOutput;
    }
}