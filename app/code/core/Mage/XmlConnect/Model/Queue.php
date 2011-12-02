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
 * @package     Mage_XmlConnect
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * XmlConnect Queue model
 *
 * @category    Mage
 * @package     Mage_Xmlconnect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_XmlConnect_Model_Queue extends Mage_Core_Model_Template
{
    /**
     * Status in queue identifier
     */
    const STATUS_IN_QUEUE   = 0;

    /**
     * Status cenceled identifier
     */
    const STATUS_CANCELED   = 1;

    /**
     * Status completed identifier
     */
    const STATUS_COMPLETED  = 2;

    /**
     * Status deleted identifier
     */
    const STATUS_DELETED    = 3;

    /**
     * Airmail message type
     */
    const MESSAGE_TYPE_AIRMAIL  = 'airmail';

    /**
     * Push notification message type
     */
    const MESSAGE_TYPE_PUSH     = 'push';

    /**
     * Notification type config path
     */
    const XML_PATH_NOTIFICATION_TYPE = 'xmlconnect/devices/%s/notification_type';

    /**
     * Count of message in queue for cron
     * config path
     */
    const XML_PATH_CRON_MESSAGES_COUNT = 'xmlconnect/mobile_application/cron_send_messages_count';

    /**
     * Current application type
     *
     * @var null|string
     */
    protected $_appType = null;

    /**
     * Initialize queue message
     *
     * @return null
     */
    protected function _construct()
    {
        $this->_init('Mage_XmlConnect_Model_Resource_Queue');
    }

    /**
     * Load object data
     *
     * @param int $id
     * @param string $field
     * @return Mage_XmlConnect_Model_Queue
     */
    public function load($id, $field = null)
    {
        parent::load($id, $field);

        if ($this->getTemplateId()) {
            $this->setName(Mage::getModel('Mage_XmlConnect_Model_Template')->load($this->getTemplateId())->getName());
        }
        return $this;
    }

    /**
     * Get template type
     *
     * @return int
     */
    public function getType()
    {
        return self::TYPE_HTML;
    }

    /**
     * Getter for application type
     * @return string
     */
    public function getApplicationType()
    {
        if (empty($this->_appType) && $this->getAppCode()) {
            $app = Mage::getModel('Mage_XmlConnect_Model_Application')->loadByCode($this->getAppCode());
            $this->_appType = $app->getId() ? $app->getType() : null;
        }

        return $this->_appType;
    }

    /**
     * Getter for application name
     *
     * @return string
     */
    public function getAppName()
    {
        if ($this->getApplicationName()) {
            return $this->getApplicationName();
        } else {
            return Mage::helper('Mage_XmlConnect_Helper_Data')->getApplicationName($this->getAppCode());
        }
    }

    /**
     * Getter for template name
     *
     * @return string
     */
    public function getTplName()
    {
        if ($this->getTemplateName()) {
            return $this->getTemplateName();
        } else {
            return Mage::helper('Mage_XmlConnect_Helper_Data')->getTemplateName($this->getTemplateId());
        }
    }

    /**
     * Retrieve processed template
     *
     * @param array $variables
     * @return string
     */
    public function getProcessedTemplate(array $variables = array())
    {
        /* @var $processor Mage_Widget_Model_Template_Filter */
        $processor = Mage::getModel('Mage_Widget_Model_Template_Filter');

        $variables['this'] = $this;

        if (Mage::app()->isSingleStoreMode()) {
            $processor->setStoreId(Mage::app()->getStore());
        } else {
            $processor->setStoreId(1);
        }

        $htmlDescription = <<<EOT
<div style="font-size: 0.8em; text-decoration: underline; margin-top: 1.5em; line-height: 2em;">%s:</div>
EOT;

        switch ($this->getData('type')) {
            case Mage_XmlConnect_Model_Queue::MESSAGE_TYPE_AIRMAIL:
                $html  = sprintf($htmlDescription, Mage::helper('Mage_XmlConnect_Helper_Data')->__('Push title'))
                    . $this->getPushTitle()
                    . sprintf($htmlDescription, Mage::helper('Mage_XmlConnect_Helper_Data')->__('Message title'))
                    . $this->getMessageTitle()
                    . sprintf($htmlDescription, Mage::helper('Mage_XmlConnect_Helper_Data')->__('Message content'))
                    . $processor->filter($this->getContent());
                break;
            case Mage_XmlConnect_Model_Queue::MESSAGE_TYPE_PUSH:
            default:
                $html  = sprintf($htmlDescription, Mage::helper('Mage_XmlConnect_Helper_Data')->__('Push title'))
                    . $this->getPushTitle();
                break;
        }
        return $html;
    }

    /**
     * Reset all model data
     *
     * @return Mage_XmlConnect_Model_Queue
     */
    public function reset()
    {
        $this->setData(array());
        $this->setOrigData();

        return $this;
    }


    /**
     * Get JSON-encoded params for broadcast AirMail
     *  Format of JSON data:
     *  {
     *      "push": {
     *          "aps": {
     *              "alert": "New message!"
     *          }
     *      },
     *      "title": "Message title",
     *      "message": "Your full message here.",
     *      "extra": {
     *          "some_key": "some_value"
     *      }
     *  }
     *
     * @return string
     */
    public function getAirmailBroadcastParams()
    {
        $notificationType = Mage::getStoreConfig(
            sprintf(Mage_XmlConnect_Model_Queue::XML_PATH_NOTIFICATION_TYPE, $this->getApplicationType())
        );

        $payload = array(
            'push' => array($notificationType => array('alert' => $this->getPushTitle())),
            'title' => $this->getMessageTitle(),
            'message' => $this->getContent(),
        );
        return Mage::helper('Mage_Core_Helper_Data')->jsonEncode($payload);
    }

    /**
     * Get JSON-encoded params for broadcast Push Notification
     *  Format of JSON data:
     *  {
     *      "aps": {
     *           "badge": 15,
     *           "alert": "Hello from Urban Airship!",
     *           "sound": "cat.caf"
     *      },
     *      "exclude_tokens": [
     *          "device token you want to skip",
     *          "another device token you want to skip"
     *      ]
     *  }
     *
     * @return string
     */
    public function getPushBroadcastParams()
    {
        $notificationType = Mage::getStoreConfig(
            sprintf(Mage_XmlConnect_Model_Queue::XML_PATH_NOTIFICATION_TYPE, $this->getApplicationType())
        );

        $payload = array(
            $notificationType => array(
//                'badge' => 'auto',
                'alert' => $this->getPushTitle(),
                'sound' => 'default'
            )
        );
        return Mage::helper('Mage_Core_Helper_Data')->jsonEncode($payload);
    }

    /**
     * Save object data
     *
     * @return Mage_Core_Model_Abstract
     */
    public function save()
    {
        if (!$this->getIsSent() && $this->getStatus() == self::STATUS_IN_QUEUE) {
            try {
                Mage::dispatchEvent('before_save_message_queue', array('queueMessage' => $this));
            } catch (Exception $e) {
                Mage::logException($e);
            }
        }
        return parent::save();
    }
}
