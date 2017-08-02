<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Controller\Product;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class \Magento\Catalog\Controller\Product\Gallery
 *
 * @since 2.0.0
 */
class Gallery extends \Magento\Catalog\Controller\Product
{
    /**
     * @var Result\ForwardFactory
     * @since 2.0.0
     */
    protected $resultForwardFactory;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     * @since 2.0.0
     */
    protected $resultPageFactory;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Result\ForwardFactory $resultForwardFactory
     * @param PageFactory $resultPageFactory
     * @since 2.0.0
     */
    public function __construct(
        Context $context,
        Result\ForwardFactory $resultForwardFactory,
        PageFactory $resultPageFactory
    ) {
        $this->resultForwardFactory = $resultForwardFactory;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * View product gallery action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     * @since 2.0.0
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
