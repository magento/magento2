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
class Mage_Webhook_Model_Webapi_WebhookData
{
    /**
     * Subscriber Name
     *
     * @var string
     */
    public $name;

    /**
     * Subscriber endpoint Url
     * @var string
     */
    public $endpoint_url;

    /**
     * Authentication type
     *
     * @var string
     */
    public $authentication_type = Mage_Webhook_Model_Subscriber::AUTH_TYPE_HMAC;

    /**
     * Registration mechanism
     *
     * @var string
     */
    public $registration_mechanism = Mage_Webhook_Model_Subscriber::REGISTRATION_MECHANISM_MANUAL;

    /**
     * Message mapping
     *
     * @var string
     */
    public $mapping = Mage_Webhook_Model_Subscriber::MAPPING_DEFAULT;

    /**
     * Message format
     *
     * @var string
     */
    public $format = Mage_Webhook_Model_Subscriber::FORMAT_JSON;

    /**
     * WebHook subscriber status - active or inactive
     *
     * @var string
     * @optional
     */
    public $status;

    /**
     * Name of the hooks
     *
     * @var string[]
     */
    public $topics;

    private static $_subscriber_data_fields = array('subscriber_id', 'name', 'endpoint_url', 'updated_at', 'authentication_type', 'registration_mechanism', 'mapping', 'format', 'status');

    /**
     * Return subscriber data field names
     * @return array
     */
    public static function getSubscriberDataFields()
    {
        return self::$_subscriber_data_fields;
    }

    /**
     * Subscriber status string
     *
     * @var string
     */
    const STATUS_ACTIVE_STRING = 'Active';
    const STATUS_INACTIVE_STRING = 'Inactive';
	const STATUS_REVOKED_STRING = 'Revoked';

    /**
     * Get subscriber status in string based on the given integer status
     *
     * @param $status
     * @return string
     */
    public static function getSubscriberStatusString($status)
    {
        if ($status == Mage_Webhook_Model_Subscriber::STATUS_ACTIVE) {
            return self::STATUS_ACTIVE_STRING;
        }

        if ($status == Mage_Webhook_Model_Subscriber::STATUS_INACTIVE) {
            return self::STATUS_INACTIVE_STRING;
        }

        if ($status == Mage_Webhook_Model_Subscriber::STATUS_REVOKED) {
            return self::STATUS_REVOKED_STRING;
        }

        return $status;
    }
}
