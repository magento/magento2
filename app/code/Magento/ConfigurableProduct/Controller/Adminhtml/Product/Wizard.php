<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Controller\Adminhtml\Product;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Catalog\Controller\Adminhtml\Product\Builder;
use Magento\Backend\App\Action\Context;

/**
 * Class Wizard
 */
class Wizard extends Action
{
    /**
     * @var Builder
     */
    protected $productBuilder;

    /**
     * @param Context $context
     * @param Builder $productBuilder
     */
    public function __construct(Context $context, Builder $productBuilder)
    {
        parent::__construct($context);
        $this->productBuilder = $productBuilder;
    }

    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $this->productBuilder->build($this->getRequest());

        /** @var \Magento\Framework\View\Result\Layout $resultLayout */
        $resultLayout = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultLayout->getLayout()->getUpdate()->removeHandle('default');

        return $resultLayout;
    }
}
