<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Block\Adminhtml\Order\View;

use Magento\Framework\View\Element\Message\InterpretationStrategyInterface;
use Magento\Sales\Model\Order;

/**
 * Order view messages
 *
 * @api
 * @since 100.0.2
 */
class Messages extends \Magento\Framework\View\Element\Messages
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $coreRegistry = null;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Message\Factory $messageFactory
     * @param \Magento\Framework\Message\CollectionFactory $collectionFactory
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param InterpretationStrategyInterface $interpretationStrategy
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Message\Factory $messageFactory,
        \Magento\Framework\Message\CollectionFactory $collectionFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        InterpretationStrategyInterface $interpretationStrategy,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $messageFactory,
            $collectionFactory,
            $messageManager,
            $interpretationStrategy,
            $data
        );
        $this->coreRegistry = $registry;
    }

    /**
     * Retrieve order model instance
     *
     * @return Order
     */
    protected function _getOrder()
    {
        return $this->coreRegistry->registry('sales_order');
    }

    /**
     * Preparing global layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        /**
         * Check Item products existing
         */
        $productIds = [];
        foreach ($this->_getOrder()->getAllItems() as $item) {
            $productIds[] = $item->getProductId();
        }

        return parent::_prepareLayout();
    }
}
