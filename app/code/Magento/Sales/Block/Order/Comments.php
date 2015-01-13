<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Order;

class Comments extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Sales\Model\Resource\Order\Invoice\Comment\CollectionFactory
     */
    protected $_invoiceCollectionFactory;

    /**
     * @var \Magento\Sales\Model\Resource\Order\Creditmemo\Comment\CollectionFactory
     */
    protected $_memoCollectionFactory;

    /**
     * @var \Magento\Sales\Model\Resource\Order\Shipment\Comment\CollectionFactory
     */
    protected $_shipmentCollectionFactory;

    /**
     * Current entity (model instance) with getCommentsCollection() method
     *
     * @var \Magento\Sales\Model\AbstractModel
     */
    protected $_entity;

    /**
     * Current comments collection
     *
     * @var \Magento\Sales\Model\Resource\Order\Comment\Collection\AbstractCollection
     */
    protected $_commentCollection;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Sales\Model\Resource\Order\Invoice\Comment\CollectionFactory $invoiceCollectionFactory
     * @param \Magento\Sales\Model\Resource\Order\Creditmemo\Comment\CollectionFactory $memoCollectionFactory
     * @param \Magento\Sales\Model\Resource\Order\Shipment\Comment\CollectionFactory $shipmentCollectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Sales\Model\Resource\Order\Invoice\Comment\CollectionFactory $invoiceCollectionFactory,
        \Magento\Sales\Model\Resource\Order\Creditmemo\Comment\CollectionFactory $memoCollectionFactory,
        \Magento\Sales\Model\Resource\Order\Shipment\Comment\CollectionFactory $shipmentCollectionFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_invoiceCollectionFactory = $invoiceCollectionFactory;
        $this->_memoCollectionFactory = $memoCollectionFactory;
        $this->_shipmentCollectionFactory = $shipmentCollectionFactory;
    }

    /**
     * Sets comments parent model instance
     *
     * @param \Magento\Sales\Model\AbstractModel $entity
     * @return $this
     */
    public function setEntity($entity)
    {
        $this->_entity = $entity;
        $this->_commentCollection = null;
        // Changing model and resource model can lead to change of comment collection
        return $this;
    }

    /**
     * Gets comments parent model instance
     *
     * @return \Magento\Sales\Model\AbstractModel
     */
    public function getEntity()
    {
        return $this->_entity;
    }

    /**
     * Initialize model comments and return comment collection
     *
     * @return \Magento\Sales\Model\Resource\Order\Comment\Collection\AbstractCollection
     * @throws \Magento\Framework\Model\Exception
     */
    public function getComments()
    {
        if (is_null($this->_commentCollection)) {
            $entity = $this->getEntity();
            if ($entity instanceof \Magento\Sales\Model\Order\Invoice) {
                $this->_commentCollection = $this->_invoiceCollectionFactory->create();
            } elseif ($entity instanceof \Magento\Sales\Model\Order\Creditmemo) {
                $this->_commentCollection = $this->_memoCollectionFactory->create();
            } elseif ($entity instanceof \Magento\Sales\Model\Order\Shipment) {
                $this->_commentCollection = $this->_shipmentCollectionFactory->create();
            } else {
                throw new \Magento\Framework\Model\Exception(__('We found an invalid entity model.'));
            }

            $this->_commentCollection->setParentFilter($entity)->setCreatedAtOrder()->addVisibleOnFrontFilter();
        }

        return $this->_commentCollection;
    }

    /**
     * Returns whether there are comments to show on frontend
     *
     * @return bool
     */
    public function hasComments()
    {
        return $this->getComments()->count() > 0;
    }
}
