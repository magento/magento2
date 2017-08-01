<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Controller\Adminhtml\Product;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class \Magento\ConfigurableProduct\Controller\Adminhtml\Product\AddAttribute
 *
 * @since 2.0.0
 */
class AddAttribute extends Action
{
    /**
     * @var \Magento\Catalog\Controller\Adminhtml\Product\Builder
     * @since 2.0.0
     */
    protected $productBuilder;

    /**
     * @param Action\Context $context
     * @param \Magento\Catalog\Controller\Adminhtml\Product\Builder $productBuilder
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Catalog\Controller\Adminhtml\Product\Builder $productBuilder
    ) {
        $this->productBuilder = $productBuilder;
        parent::__construct($context);
    }

    /**
     * @inheritdoc
     * @since 2.0.0
     */
    public function execute()
    {
        $this->productBuilder->build($this->getRequest());
        return $this->resultFactory->create(ResultFactory::TYPE_LAYOUT);
    }
}
