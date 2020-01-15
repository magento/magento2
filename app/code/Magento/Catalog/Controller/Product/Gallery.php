<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Controller\Product;

use Magento\Catalog\Controller\Product;
use Magento\Catalog\Helper\Product as ProductHelper;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;

/**
 * Product gallery controller.
 */
class Gallery extends Product implements HttpGetActionInterface, HttpPostActionInterface
{
    /**
     * @var Result\ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Context $context
     * @param ProductHelper $productHelper
     * @param Result\ForwardFactory $resultForwardFactory
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        ProductHelper $productHelper,
        Result\ForwardFactory $resultForwardFactory,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context, $productHelper);
        $this->resultForwardFactory = $resultForwardFactory;
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * View product gallery action
     *
     * @return ResultInterface
     */
    public function execute()
    {
        $result = null;
        if (!$this->_initProduct()) {
            $store = $this->getRequest()->getQuery('store');
            if (isset($store) && !$this->getResponse()->isRedirect()) {
                $result = $this->resultRedirectFactory->create();
                $result->setPath('');
            } elseif (!$this->getResponse()->isRedirect()) {
                $result = $this->resultForwardFactory->create();
                $result->forward('noroute');
            }
        }
        return $result ?: $this->resultPageFactory->create();
    }
}
