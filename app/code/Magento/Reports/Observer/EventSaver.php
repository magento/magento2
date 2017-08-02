<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Reports\Observer;

/**
 * Reports Event observer model
 * @since 2.0.0
 */
class EventSaver
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     * @since 2.0.0
     */
    protected $_storeManager;

    /**
     * @var \Magento\Reports\Model\EventFactory
     * @since 2.0.0
     */
    protected $_eventFactory;

    /**
     * @var \Magento\Customer\Model\Session
     * @since 2.0.0
     */
    protected $_customerSession;

    /**
     * @var \Magento\Customer\Model\Visitor
     * @since 2.0.0
     */
    protected $_customerVisitor;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Reports\Model\EventFactory $event
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Model\Visitor $customerVisitor
     * @since 2.0.0
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
     * @since 2.0.0
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
