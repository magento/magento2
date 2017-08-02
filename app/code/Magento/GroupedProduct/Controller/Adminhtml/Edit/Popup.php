<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GroupedProduct\Controller\Adminhtml\Edit;

use Magento\Backend\App\AbstractAction;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Catalog\Model\ProductFactory;
use Psr\Log\LoggerInterface;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class \Magento\GroupedProduct\Controller\Adminhtml\Edit\Popup
 *
 * @since 2.0.0
 */
class Popup extends AbstractAction
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Catalog::products';

    /**
     * @var \Magento\Framework\Registry
     * @since 2.0.0
     */
    protected $registry;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     * @since 2.0.0
     */
    protected $factory;

    /**
     * @var \Psr\Log\LoggerInterface
     * @since 2.0.0
     */
    protected $logger;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Catalog\Model\ProductFactory $factory
     * @param \Psr\Log\LoggerInterface $logger
     * @since 2.0.0
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ProductFactory $factory,
        LoggerInterface $logger
    ) {
        $this->registry = $registry;
        $this->factory = $factory;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * Get associated grouped products grid popup
     *
     * @return \Magento\Framework\View\Result\Layout
     * @since 2.0.0
     */
    public function execute()
    {
        $productId = (int)$this->getRequest()->getParam('id');

        /** @var $product \Magento\Catalog\Model\Product */
        $product = $this->factory->create();
        $product->setStoreId($this->getRequest()->getParam('store', 0));

        $typeId = $this->getRequest()->getParam('type');
        if (!$productId && $typeId) {
            $product->setTypeId($typeId);
        }
        $product->setData('_edit_mode', true);

        if ($productId) {
            try {
                $product->load($productId);
            } catch (\Exception $e) {
                $product->setTypeId(\Magento\Catalog\Model\Product\Type::DEFAULT_TYPE);
                $this->logger->critical($e);
            }
        }

        $setId = (int)$this->getRequest()->getParam('set');
        if ($setId) {
            $product->setAttributeSetId($setId);
        }
        $this->registry->register('current_product', $product);
        /** @var \Magento\Framework\View\Result\Layout $resultLayout */
        $resultLayout = $this->resultFactory->create(ResultFactory::TYPE_LAYOUT);
        return $resultLayout;
    }
}
