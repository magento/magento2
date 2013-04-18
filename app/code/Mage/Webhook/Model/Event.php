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
class Mage_Webhook_Model_Event extends Mage_Core_Model_Abstract implements Mage_Webhook_Model_Event_Interface
{
    const PREPARING     = 0;
    const READY_TO_SEND = 1;
    const PROCESSED     = 2;

    public function _construct()
    {
        parent::_construct();
        $this->_init('Mage_Webhook_Model_Resource_Event');
    }

    /**
     * Prepare data to be saved to database
     * @return Mage_Core_Model_Abstract
     */
    protected function _beforeSave()
    {
        parent::_beforeSave();

        if ($this->isObjectNew()) {
            $this->setCreatedAt($this->_getResource()->formatDate(true));
        } elseif ($this->getId() && !$this->hasData('updated_at')) {
            $this->setUpdatedAt($this->_getResource()->formatDate(true));
        }
        return $this;
    }

    public function setBodyData(array $data)
    {
        return $this->setData('body_data', serialize($data));
    }

    public function getBodyData()
    {
        $data = $this->getData('body_data');
        if (!is_null($data)) {
            return unserialize($data);
        }
        return array();
    }

    public function setHeaders(array $headers)
    {
        return $this->setData('headers', serialize($headers));
    }

    public function getHeaders()
    {
        $headers = $this->getData('headers');
        if (!is_null($headers)) {
            return unserialize($headers);
        }
        return array();
    }

    public function setOptions(array $options)
    {
        return $this->setData('options', serialize($options));
    }

    public function getOptions()
    {
        $options = $this->getData('options');
        if (!is_null($options)) {
            return unserialize($options);
        }
        return array();
    }

    public function getMapping()
    {
        return $this->getData('mapping');
    }

    public function getStatus()
    {
        return $this->getData('status');
    }

    public function getTopic()
    {
        return $this->getData('topic');
    }

    public function setTopic($topic)
    {
        return $this->setData('topic', $topic);
    }
}
