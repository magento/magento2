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
 * @package     Mage_Newsletter
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Newsletter queue model.
 *
 * @method Mage_Newsletter_Model_Resource_Queue _getResource()
 * @method Mage_Newsletter_Model_Resource_Queue getResource()
 * @method int getTemplateId()
 * @method Mage_Newsletter_Model_Queue setTemplateId(int $value)
 * @method int getNewsletterType()
 * @method Mage_Newsletter_Model_Queue setNewsletterType(int $value)
 * @method string getNewsletterText()
 * @method Mage_Newsletter_Model_Queue setNewsletterText(string $value)
 * @method string getNewsletterStyles()
 * @method Mage_Newsletter_Model_Queue setNewsletterStyles(string $value)
 * @method string getNewsletterSubject()
 * @method Mage_Newsletter_Model_Queue setNewsletterSubject(string $value)
 * @method string getNewsletterSenderName()
 * @method Mage_Newsletter_Model_Queue setNewsletterSenderName(string $value)
 * @method string getNewsletterSenderEmail()
 * @method Mage_Newsletter_Model_Queue setNewsletterSenderEmail(string $value)
 * @method int getQueueStatus()
 * @method Mage_Newsletter_Model_Queue setQueueStatus(int $value)
 * @method string getQueueStartAt()
 * @method Mage_Newsletter_Model_Queue setQueueStartAt(string $value)
 * @method string getQueueFinishAt()
 * @method Mage_Newsletter_Model_Queue setQueueFinishAt(string $value)
 *
 * @category    Mage
 * @package     Mage_Newsletter
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Newsletter_Model_Queue extends Mage_Core_Model_Template
{
    /**
     * Newsletter Template object
     *
     * @var Mage_Newsletter_Model_Template
     */
    protected $_template;

    /**
     * @var Mage_Core_Model_Email_Template
     */
    protected $_emailTemplate = null;

    /**
     * Subscribers collection
     * @var Varien_Data_Collection_Db
     */
    protected $_subscribersCollection = null;

    /**
     * Save stores flag.
     *
     * @var boolean
     */
    protected $_saveStoresFlag = false;

    /**
     * Stores assigned to queue.
     *
     * @var array
     */
    protected $_stores = array();

    const STATUS_NEVER = 0;
    const STATUS_SENDING = 1;
    const STATUS_CANCEL = 2;
    const STATUS_SENT = 3;
    const STATUS_PAUSE = 4;

    /**
     * Initialize resource model
     */
    protected function _construct()
    {
        parent::_construct();
        $emailTemplate = $this->_getData('email_template');
        if ($emailTemplate) {
            $this->unsetData('email_template');
            if (!($emailTemplate instanceof Mage_Core_Model_Email_Template)) {
                throw new Exception('Instance of Mage_Core_Model_Email_Template is expected.');
            }
            $this->_emailTemplate = $emailTemplate;
        }
        $this->_init('Mage_Newsletter_Model_Resource_Queue');
    }

    /**
     * Return: is this queue newly created or not.
     *
     * @return boolean
     */
    public function isNew()
    {
        return (is_null($this->getQueueStatus()));
    }

    /**
     * Returns subscribers collection for this queue
     *
     * @return Varien_Data_Collection_Db
     */
    public function getSubscribersCollection()
    {
        if (is_null($this->_subscribersCollection)) {
            $this->_subscribersCollection = Mage::getResourceModel(
                    'Mage_Newsletter_Model_Resource_Subscriber_Collection'
                )
                ->useQueue($this);
        }

        return $this->_subscribersCollection;
    }

    /**
     * Set $_data['queue_start'] based on string from backend, which based on locale.
     *
     * @param string|null $startAt start date of the mailing queue
     * @return Mage_Newsletter_Model_Queue
     */
    public function setQueueStartAtByString($startAt)
    {
        if(is_null($startAt) || $startAt == '') {
            $this->setQueueStartAt(null);
        } else {
            $locale = Mage::app()->getLocale();
            $format = $locale->getDateTimeFormat(Mage_Core_Model_Locale::FORMAT_TYPE_MEDIUM);
            $time = $locale->date($startAt, $format)->getTimestamp();
            $this->setQueueStartAt(Mage::getModel('Mage_Core_Model_Date')->gmtDate(null, $time));
        }
        return $this;
     }

    /**
     * Send messages to subscribers for this queue
     *
     * @param   int     $count
     * @param   array   $additionalVariables
     * @return Mage_Newsletter_Model_Queue
     */
    public function sendPerSubscriber($count = 20, array $additionalVariables = array())
    {
        if ($this->getQueueStatus() != self::STATUS_SENDING
           && ($this->getQueueStatus() != self::STATUS_NEVER && $this->getQueueStartAt())
        ) {
            return $this;
        }

        if ($this->getSubscribersCollection()->getSize() == 0) {
            $this->_finishQueue();
            return $this;
        }

        $collection = $this->getSubscribersCollection()
            ->useOnlyUnsent()
            ->showCustomerInfo()
            ->setPageSize($count)
            ->setCurPage(1)
            ->load();

        /** @var Mage_Core_Model_Email_Template $sender */
        $sender = $this->_emailTemplate ?: Mage::getModel('Mage_Core_Model_Email_Template');
        $sender->setSenderName($this->getNewsletterSenderName())
            ->setSenderEmail($this->getNewsletterSenderEmail())
            ->setTemplateType(self::TYPE_HTML)
            ->setTemplateSubject($this->getNewsletterSubject())
            ->setTemplateText($this->getNewsletterText())
            ->setTemplateStyles($this->getNewsletterStyles())
            ->setTemplateFilter(Mage::helper('Mage_Newsletter_Helper_Data')->getTemplateProcessor());

        /** @var Mage_Newsletter_Model_Subscriber $item */
        foreach ($collection->getItems() as $item) {
            $email = $item->getSubscriberEmail();
            $name = $item->getSubscriberFullName();

            $sender->emulateDesign($item->getStoreId());
            $successSend = $sender->send($email, $name, array('subscriber' => $item));
            $sender->revertDesign();

            if ($successSend) {
                $item->received($this);
            } else {
                /** @var Mage_Newsletter_Model_Problem $problem */
                $problem = Mage::getModel('Mage_Newsletter_Model_Problem');
                $problem->addSubscriberData($item);
                $problem->addQueueData($this);
                $e = $sender->getSendingException();
                if ($e) {
                    $problem->addErrorData($e);
                }
                $problem->save();
                $item->received($this);
            }
        }

        if (count($collection->getItems()) < $count-1 || count($collection->getItems()) == 0) {
            $this->_finishQueue();
        }
        return $this;
    }

    /**
     * Finish queue: set status SENT and update finish date
     *
     * @return Mage_Newsletter_Model_Queue
     */
    protected function _finishQueue()
    {
        $this->setQueueFinishAt(Mage::getSingleton('Mage_Core_Model_Date')->gmtDate());
        $this->setQueueStatus(self::STATUS_SENT);
        $this->save();

        return $this;
    }

    /**
     * Getter data for saving
     *
     * @return array
     */
    public function getDataForSave()
    {
        $data = array();
        $data['template_id'] = $this->getTemplateId();
        $data['queue_status'] = $this->getQueueStatus();
        $data['queue_start_at'] = $this->getQueueStartAt();
        $data['queue_finish_at'] = $this->getQueueFinishAt();
        return $data;
    }

    /**
     * Add subscribers to queue.
     *
     * @param array $subscriberIds
     * @return Mage_Newsletter_Model_Queue
     */
    public function addSubscribersToQueue(array $subscriberIds)
    {
        $this->_getResource()->addSubscribersToQueue($this, $subscriberIds);
        return $this;
    }

    /**
     * Setter for save stores flag.
     *
     * @param boolean|integer|string $value
     * @return Mage_Newsletter_Model_Queue
     */
    public function setSaveStoresFlag($value)
    {
        $this->_saveStoresFlag = (boolean)$value;
        return $this;
    }

    /**
     * Getter for save stores flag.
     *
     * @param void
     * @return boolean
     */
    public function getSaveStoresFlag()
    {
        return $this->_saveStoresFlag;
    }

    /**
     * Setter for stores of queue.
     *
     * @param array
     * @return Mage_Newsletter_Model_Queue
     */
    public function setStores(array $storesIds)
    {
        $this->setSaveStoresFlag(true);
        $this->_stores = $storesIds;
        return $this;
    }

    /**
     * Getter for stores of queue.
     *
     * @return array
     */
    public function getStores()
    {
        if(!$this->_stores) {
            $this->_stores = $this->_getResource()->getStores($this);
        }

        return $this->_stores;
    }

    /**
     * Retrieve Newsletter Template object
     *
     * @return Mage_Newsletter_Model_Template
     */
    public function getTemplate()
    {
        if (is_null($this->_template)) {
            $this->_template = Mage::getModel('Mage_Newsletter_Model_Template')
                ->load($this->getTemplateId());
        }
        return $this->_template;
    }

    /**
     * Getter for template type
     *
     * @return int|string
     */
    public function getType()
    {
        return $this->getNewsletterType();
    }
}
