<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Model\Order\Invoice;

use Magento\Framework\Api\AttributeValueFactory;
use Magento\Sales\Api\Data\InvoiceCommentInterface;
use Magento\Sales\Model\AbstractModel;

/**
 * @method \Magento\Sales\Model\ResourceModel\Order\Invoice\Comment _getResource()
 * @method \Magento\Sales\Model\ResourceModel\Order\Invoice\Comment getResource()
 */
class Comment extends AbstractModel implements InvoiceCommentInterface
{
    /**
     * Invoice instance
     *
     * @var \Magento\Sales\Model\Order\Invoice
     */
    protected $_invoice;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory
     * @param AttributeValueFactory $customAttributeFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $resource,
            $resourceCollection,
            $data
        );
        $this->_storeManager = $storeManager;
    }

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\Sales\Model\ResourceModel\Order\Invoice\Comment');
    }

    /**
     * Declare invoice instance
     *
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     * @return $this
     */
    public function setInvoice(\Magento\Sales\Model\Order\Invoice $invoice)
    {
        $this->_invoice = $invoice;
        return $this;
    }

    /**
     * Retrieve invoice instance
     *
     * @return \Magento\Sales\Model\Order\Invoice
     */
    public function getInvoice()
    {
        return $this->_invoice;
    }

    /**
     * Get store object
     *
     * @return \Magento\Store\Model\Store
     */
    public function getStore()
    {
        if ($this->getInvoice()) {
            return $this->getInvoice()->getStore();
        }
        return $this->_storeManager->getStore();
    }

    /**
     * Returns comment
     *
     * @return string
     */
    public function getComment()
    {
        return $this->getData(InvoiceCommentInterface::COMMENT);
    }

    /**
     * Returns created_at
     *
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->getData(InvoiceCommentInterface::CREATED_AT);
    }

    /**
     * {@inheritdoc}
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(InvoiceCommentInterface::CREATED_AT, $createdAt);
    }

    /**
     * Returns is_customer_notified
     *
     * @return int
     */
    public function getIsCustomerNotified()
    {
        return $this->getData(InvoiceCommentInterface::IS_CUSTOMER_NOTIFIED);
    }

    /**
     * Returns is_visible_on_front
     *
     * @return int
     */
    public function getIsVisibleOnFront()
    {
        return $this->getData(InvoiceCommentInterface::IS_VISIBLE_ON_FRONT);
    }

    /**
     * Returns parent_id
     *
     * @return int
     */
    public function getParentId()
    {
        return $this->getData(InvoiceCommentInterface::PARENT_ID);
    }

    //@codeCoverageIgnoreStart
    /**
     * {@inheritdoc}
     */
    public function setParentId($id)
    {
        return $this->setData(InvoiceCommentInterface::PARENT_ID, $id);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsCustomerNotified($isCustomerNotified)
    {
        return $this->setData(InvoiceCommentInterface::IS_CUSTOMER_NOTIFIED, $isCustomerNotified);
    }

    /**
     * {@inheritdoc}
     */
    public function setIsVisibleOnFront($isVisibleOnFront)
    {
        return $this->setData(InvoiceCommentInterface::IS_VISIBLE_ON_FRONT, $isVisibleOnFront);
    }

    /**
     * {@inheritdoc}
     */
    public function setComment($comment)
    {
        return $this->setData(InvoiceCommentInterface::COMMENT, $comment);
    }

    /**
     * {@inheritdoc}
     *
     * @return \Magento\Sales\Api\Data\InvoiceCommentExtensionInterface|null
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }

    /**
     * {@inheritdoc}
     *
     * @param \Magento\Sales\Api\Data\InvoiceCommentExtensionInterface $extensionAttributes
     * @return $this
     */
    public function setExtensionAttributes(
        \Magento\Sales\Api\Data\InvoiceCommentExtensionInterface $extensionAttributes
    ) {
        return $this->_setExtensionAttributes($extensionAttributes);
    }
    //@codeCoverageIgnoreEnd
}
