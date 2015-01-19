<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Controller\Product\Compare;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Core\App\Action\FormKeyValidator;
use Magento\Framework\Controller\Result;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Catalog\Controller\Product\Compare
{
    /**
     * @var \Magento\Framework\Url\DecoderInterface
     */
    protected $urlDecoder;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Catalog\Model\Product\Compare\ItemFactory $compareItemFactory
     * @param \Magento\Catalog\Model\Resource\Product\Compare\Item\CollectionFactory $itemCollectionFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Customer\Model\Visitor $customerVisitor
     * @param \Magento\Catalog\Model\Product\Compare\ListCompare $catalogProductCompareList
     * @param \Magento\Catalog\Model\Session $catalogSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param FormKeyValidator $formKeyValidator
     * @param Result\RedirectFactory $resultRedirectFactory
     * @param PageFactory $resultPageFactory
     * @param ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\Url\DecoderInterface $urlDecoder
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Catalog\Model\Product\Compare\ItemFactory $compareItemFactory,
        \Magento\Catalog\Model\Resource\Product\Compare\Item\CollectionFactory $itemCollectionFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\Visitor $customerVisitor,
        \Magento\Catalog\Model\Product\Compare\ListCompare $catalogProductCompareList,
        \Magento\Catalog\Model\Session $catalogSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        FormKeyValidator $formKeyValidator,
        Result\RedirectFactory $resultRedirectFactory,
        PageFactory $resultPageFactory,
        ProductRepositoryInterface $productRepository,
        \Magento\Framework\Url\DecoderInterface $urlDecoder
    ) {
        parent::__construct(
            $context,
            $compareItemFactory,
            $itemCollectionFactory,
            $customerSession,
            $customerVisitor,
            $catalogProductCompareList,
            $catalogSession,
            $storeManager,
            $formKeyValidator,
            $resultRedirectFactory,
            $resultPageFactory,
            $productRepository
        );
        $this->urlDecoder = $urlDecoder;
    }

    /**
     * Compare index action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $items = $this->getRequest()->getParam('items');

        $beforeUrl = $this->getRequest()->getParam(self::PARAM_NAME_URL_ENCODED);
        if ($beforeUrl) {
            $this->_catalogSession->setBeforeCompareUrl(
                $this->urlDecoder->decode($beforeUrl)
            );
        }

        if ($items) {
            $items = explode(',', $items);
            /** @var \Magento\Catalog\Model\Product\Compare\ListCompare $list */
            $list = $this->_catalogProductCompareList;
            $list->addProducts($items);
            $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setPath('*/*/*');
        }
        return $this->resultPageFactory->create();
    }
}
