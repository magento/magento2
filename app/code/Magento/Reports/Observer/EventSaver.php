<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Observer;

/**
 * Reports Event observer model
 */
class EventSaver
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Reports\Model\EventFactory
     */
    protected $_eventFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Customer\Model\Visitor
     */
    protected $_customerVisitor;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Reports\Model\EventFactory $event
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Model\Visitor $customerVisitor
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Reports\Model\EventFactory $event,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\Visitor $customerVisitor
    ) {
        $this->_storeManager = $storeManager;
        $this->_eventFactory = $event;
        $this->_customerSession = $customerSession;
        $this->_customerVisitor = $customerVisitor;
    }

    /**
     * Save event
     *
     * @param int $eventTypeId
     * @param int $objectId
     * @param int|null $subjectId
     * @param int $subtype
     * @return void
     */
    public function save($eventTypeId, $objectId, $subjectId = null, $subtype = 0)
    {
        if ($subjectId === null) {
            if ($this->_customerSession->isLoggedIn()) {
                $subjectId = $this->_customerSession->getCustomerId();
            } else {
                $subjectId = $this->_customerVisitor->getId();
                $subtype = 1;
            }
        }

        /** @var \Magento\Reports\Model\Event $eventModel */
        $eventModel = $this->_eventFactory->create();
        $storeId = $this->_storeManager->getStore()->getId();
        $eventModel->setData([
            'event_type_id' => $eventTypeId,
            'object_id' => $objectId,
            'subject_id' => $subjectId,
            'subtype' => $subtype,
            'store_id' => $storeId,
        ]);

        $eventModel->save();
    }
}
