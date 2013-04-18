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
 * @package     Mage_Webhook
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
interface Mage_Webhook_Model_Formatter_Interface
{
    const CONTENT_TYPE_HEADER = 'Content-type';

    /**
     * @param Mage_Webhook_Model_Event_Interface $event
     * @return Mage_Webhook_Model_Message
     */
    public function format(Mage_Webhook_Model_Event_Interface $event);

    /**
     * @param Mage_Webhook_Model_Message_Interface $message
     * @return Mage_Webhook_Model_Message_Interface
     */
    public function decode(Mage_Webhook_Model_Message_Interface $message);
}
