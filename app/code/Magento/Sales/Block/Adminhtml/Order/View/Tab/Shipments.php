<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Adminhtml\Order\View\Tab;

/**
 * Order Shipments grid
 *
 * @api
 * @since 100.0.2
 */
class Shipments extends \Magento\Framework\View\Element\Text\ListText implements
    \Magento\Backend\Block\Widget\Tab\TabInterface
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

     /**
     * @var Magento\Framework\AuthorizationInterface
     */
    private $authorization;
    
    /**
     * Collection factory
     *
     * @param \Magento\Framework\View\Element\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\AuthorizationInterface|null $authorization
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        array $data = [],
        \Magento\Framework\AuthorizationInterface $authorization = null
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->authorization = $authorization?: \Magento\Framework\App\ObjectManager::getInstance()->get(Magento\Framework\AuthorizationInterface::class);
        parent::__construct($context, $data);
    }

    /**
     * Retrieve order model instance
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->_coreRegistry->registry('current_order');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabLabel()
    {
        return __('Shipments');
    }

    /**
     * {@inheritdoc}
     */
    public function getTabTitle()
    {
        return __('Order Shipments');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        if ($this->getOrder()->getIsVirtual() || !$this->authorization->isAllowed('Magento_Sales::ship')) {
            return false;
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
    }
}
