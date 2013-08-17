<?php
/**
 * Container for "create registration" form
 *
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
 * @package     Mage_Webhook
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
class Mage_Webhook_Block_Adminhtml_Registration_Create_Form_Container extends Mage_Backend_Block_Template
{
    /** Key used to store subscription data into the registry */
    const REGISTRY_KEY_CURRENT_SUBSCRIPTION = 'current_subscription';

    /** Keys used to retrieve values from subscription data array */
    const DATA_SUBSCRIPTION_ID = 'subscription_id';
    const DATA_NAME = 'name';

    /** @var array */
    protected $_subscriptionData;

    /**
     * @param Mage_Core_Model_Registry $registry
     * @param Mage_Backend_Block_Template_Context $context
     * @param array $data
     */
    public function __construct(
        Mage_Backend_Block_Template_Context $context,
        Mage_Core_Model_Registry $registry,
        array $data = array()
    ) {
        parent::__construct($context, $data);
        $this->_subscriptionData = $registry->registry(self::REGISTRY_KEY_CURRENT_SUBSCRIPTION);;
    }

    /**
     * Gets submit url
     *
     * @return string Form url
     */
    public function getSubmitUrl()
    {
        return $this->getUrl('*/*/register', array('id' => $this->_subscriptionData[self::DATA_SUBSCRIPTION_ID]));
    }

    /**
     * Get subscription name
     *
     * @return string
     */
    public function getSubscriptionName()
    {
        return $this->_subscriptionData[self::DATA_NAME];
    }
}
