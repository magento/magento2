<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Newsletter\Model;

/**
 * Newsletter queue model.
 *
 * @method \Magento\Newsletter\Model\Resource\Queue _getResource()
 * @method \Magento\Newsletter\Model\Resource\Queue getResource()
 * @method int getTemplateId()
 * @method \Magento\Newsletter\Model\Queue setTemplateId(int $value)
 * @method int getNewsletterType()
 * @method \Magento\Newsletter\Model\Queue setNewsletterType(int $value)
 * @method string getNewsletterText()
 * @method \Magento\Newsletter\Model\Queue setNewsletterText(string $value)
 * @method string getNewsletterStyles()
 * @method \Magento\Newsletter\Model\Queue setNewsletterStyles(string $value)
 * @method string getNewsletterSubject()
 * @method \Magento\Newsletter\Model\Queue setNewsletterSubject(string $value)
 * @method string getNewsletterSenderName()
 * @method \Magento\Newsletter\Model\Queue setNewsletterSenderName(string $value)
 * @method string getNewsletterSenderEmail()
 * @method \Magento\Newsletter\Model\Queue setNewsletterSenderEmail(string $value)
 * @method int getQueueStatus()
 * @method \Magento\Newsletter\Model\Queue setQueueStatus(int $value)
 * @method string getQueueStartAt()
 * @method \Magento\Newsletter\Model\Queue setQueueStartAt(string $value)
 * @method string getQueueFinishAt()
 * @method \Magento\Newsletter\Model\Queue setQueueFinishAt(string $value)
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class Queue extends \Magento\Email\Model\AbstractTemplate
{
    /**
     * Newsletter Template object
     *
     * @var \Magento\Newsletter\Model\Template
     */
    protected $_template;

    /**
     * Subscribers collection
     *
     * @var \Magento\Newsletter\Model\Resource\Subscriber\Collection
     */
    protected $_subscribersCollection;

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
    protected $_stores = [];

    const STATUS_NEVER = 0;

    const STATUS_SENDING = 1;

    const STATUS_CANCEL = 2;

    const STATUS_SENT = 3;

    const STATUS_PAUSE = 4;

    /**
     * Filter for newsletter text
     *
     * @var \Magento\Newsletter\Model\Template\Filter
     */
    protected $_templateFilter;

    /**
     * Date
     *
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;

    /**
     * Problem factory
     *
     * @var \Magento\Newsletter\Model\ProblemFactory
     */
    protected $_problemFactory;

    /**
     * Template factory
     *
     * @var \Magento\Newsletter\Model\TemplateFactory
     */
    protected $_templateFactory;

    /**
     * @var \Magento\Newsletter\Model\Queue\TransportBuilder
     */
    protected $_transportBuilder;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\View\DesignInterface $design
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Core\Model\App\Emulation $appEmulation
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Newsletter\Model\Template\Filter $templateFilter
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Newsletter\Model\TemplateFactory $templateFactory
     * @param \Magento\Newsletter\Model\ProblemFactory $problemFactory
     * @param \Magento\Newsletter\Model\Resource\Subscriber\CollectionFactory $subscriberCollectionFactory
     * @param \Magento\Newsletter\Model\Queue\TransportBuilder $transportBuilder
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\View\DesignInterface $design,
        \Magento\Framework\Registry $registry,
        \Magento\Core\Model\App\Emulation $appEmulation,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Newsletter\Model\Template\Filter $templateFilter,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Newsletter\Model\TemplateFactory $templateFactory,
        \Magento\Newsletter\Model\ProblemFactory $problemFactory,
        \Magento\Newsletter\Model\Resource\Subscriber\CollectionFactory $subscriberCollectionFactory,
        \Magento\Newsletter\Model\Queue\TransportBuilder $transportBuilder,
        array $data = []
    ) {
        parent::__construct($context, $design, $registry, $appEmulation, $storeManager, $data);
        $this->_templateFilter = $templateFilter;
        $this->_date = $date;
        $this->_localeDate = $localeDate;
        $this->_templateFactory = $templateFactory;
        $this->_problemFactory = $problemFactory;
        $this->_subscribersCollection = $subscriberCollectionFactory->create();
        $this->_transportBuilder = $transportBuilder;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('Magento\Newsletter\Model\Resource\Queue');
    }

    /**
     * Return: is this queue newly created or not.
     *
     * @return boolean
     */
    public function isNew()
    {
        return is_null($this->getQueueStatus());
    }

    /**
     * Set $_data['queue_start'] based on string from backend, which based on locale.
     *
     * @param string|null $startAt start date of the mailing queue
     * @return $this
     */
    public function setQueueStartAtByString($startAt)
    {
        if (is_null($startAt) || $startAt == '') {
            $this->setQueueStartAt(null);
        } else {
            $format = $this->_localeDate->getDateTimeFormat(
                \Magento\Framework\Stdlib\DateTime\TimezoneInterface::FORMAT_TYPE_MEDIUM
            );
            $time = $this->_localeDate->date($startAt, $format)->getTimestamp();
            $this->setQueueStartAt($this->_date->gmtDate(null, $time));
        }
        return $this;
    }

    /**
     * Send messages to subscribers for this queue
     *
     * @param int $count
     * @return $this
     */
    public function sendPerSubscriber($count = 20)
    {
        if ($this->getQueueStatus() != self::STATUS_SENDING &&
            ($this->getQueueStatus() != self::STATUS_NEVER &&
            $this->getQueueStartAt())
        ) {
            return $this;
        }

        if (!$this->_subscribersCollection->getQueueJoinedFlag()) {
            $this->_subscribersCollection->useQueue($this);
        }

        if ($this->_subscribersCollection->getSize() == 0) {
            $this->_finishQueue();
            return $this;
        }

        $collection = $this->_subscribersCollection->useOnlyUnsent()->showCustomerInfo()->setPageSize(
            $count
        )->setCurPage(
            1
        )->load();

        $this->_transportBuilder->setTemplateData(
            [
                'template_subject' => $this->getNewsletterSubject(),
                'template_text' => $this->getNewsletterText(),
                'template_styles' => $this->getNewsletterStyles(),
                'template_filter' => $this->_templateFilter,
                'template_type' => self::TYPE_HTML,
            ]
        );

        /** @var \Magento\Newsletter\Model\Subscriber $item */
        foreach ($collection->getItems() as $item) {
            $transport = $this->_transportBuilder->setTemplateOptions(
                ['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 'store' => $item->getStoreId()]
            )->setTemplateVars(
                ['subscriber' => $item]
            )->setFrom(
                ['name' => $this->getNewsletterSenderEmail(), 'email' => $this->getNewsletterSenderName()]
            )->addTo(
                $item->getSubscriberEmail(),
                $item->getSubscriberFullName()
            )->getTransport();

            try {
                $transport->sendMessage();
            } catch (\Magento\Framework\Mail\Exception $e) {
                /** @var \Magento\Newsletter\Model\Problem $problem */
                $problem = $this->_problemFactory->create();
                $problem->addSubscriberData($item);
                $problem->addQueueData($this);
                $problem->addErrorData($e);
                $problem->save();
            }
            $item->received($this);
        }

        if (count($collection->getItems()) < $count - 1 || count($collection->getItems()) == 0) {
            $this->_finishQueue();
        }
        return $this;
    }

    /**
     * Finish queue: set status SENT and update finish date
     *
     * @return $this
     */
    protected function _finishQueue()
    {
        $this->setQueueFinishAt($this->_date->gmtDate());
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
        $data = [];
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
     * @return $this
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
     * @return $this
     */
    public function setSaveStoresFlag($value)
    {
        $this->_saveStoresFlag = (bool)$value;
        return $this;
    }

    /**
     * Getter for save stores flag.
     *
     * @return boolean
     */
    public function getSaveStoresFlag()
    {
        return $this->_saveStoresFlag;
    }

    /**
     * Setter for stores of queue.
     *
     * @param array $storesIds
     * @return $this
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
        if (!$this->_stores) {
            $this->_stores = $this->_getResource()->getStores($this);
        }

        return $this->_stores;
    }

    /**
     * Retrieve Newsletter Template object
     *
     * @return \Magento\Newsletter\Model\Template
     */
    public function getTemplate()
    {
        if (is_null($this->_template)) {
            $this->_template = $this->_templateFactory->create()->load($this->getTemplateId());
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
