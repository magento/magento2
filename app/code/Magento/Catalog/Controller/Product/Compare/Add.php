<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Controller\Product\Compare;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Controller\Product\Compare;
use Magento\Catalog\Helper\Product\Compare as CompareHelper;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Compare\ItemFactory;
use Magento\Catalog\Model\Product\Compare\ListCompare;
use Magento\Catalog\Model\ResourceModel\Product\Compare\Item\CollectionFactory;
use Magento\Catalog\ViewModel\Product\Checker\AddToCompareAvailability;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Visitor;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Add item to compare list action.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Add extends Compare implements HttpPostActionInterface
{
    /**
     * @var AddToCompareAvailability
     */
    private $compareAvailability;

    /**
     * @var CompareHelper
     */
    private $compareHelper;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @param Context $context
     * @param ItemFactory $compareItemFactory
     * @param CollectionFactory $itemCollectionFactory
     * @param Session $customerSession
     * @param Visitor $customerVisitor
     * @param ListCompare $catalogProductCompareList
     * @param \Magento\Catalog\Model\Session $catalogSession
     * @param StoreManagerInterface $storeManager
     * @param Validator $formKeyValidator
     * @param PageFactory $resultPageFactory
     * @param ProductRepositoryInterface $productRepository
     * @param AddToCompareAvailability $compareAvailability
     * @param CompareHelper $compareHelper
     * @param Escaper $escaper
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        ItemFactory $compareItemFactory,
        CollectionFactory $itemCollectionFactory,
        Session $customerSession,
        Visitor $customerVisitor,
        ListCompare $catalogProductCompareList,
        \Magento\Catalog\Model\Session $catalogSession,
        StoreManagerInterface $storeManager,
        Validator $formKeyValidator,
        PageFactory $resultPageFactory,
        ProductRepositoryInterface $productRepository,
        AddToCompareAvailability $compareAvailability,
        CompareHelper $compareHelper,
        Escaper $escaper
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
            $resultPageFactory,
            $productRepository
        );

        $this->compareAvailability = $compareAvailability;
        $this->compareHelper = $compareHelper;
        $this->escaper = $escaper;
    }

    /**
     * Add item to compare list.
     *
     * @return ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            return $resultRedirect->setRefererUrl();
        }

        $productId = (int)$this->getRequest()->getParam('product');
        if ($productId && ($this->_customerVisitor->getId() || $this->_customerSession->isLoggedIn())) {
            $storeId = $this->_storeManager->getStore()->getId();
            try {
                /** @var Product $product */
                $product = $this->productRepository->getById($productId, false, $storeId);
            } catch (NoSuchEntityException $e) {
                $product = null;
            }

            if ($product && $this->compareAvailability->isAvailableForCompare($product)) {
                $this->_catalogProductCompareList->addProduct($product);
                $productName = $this->escaper->escapeHtml($product->getName());
                $this->messageManager->addComplexSuccessMessage(
                    'addCompareSuccessMessage',
                    [
                        'product_name' => $productName,
                        'compare_list_url' => $this->_url->getUrl('catalog/product_compare'),
                    ]
                );

                $this->_eventManager->dispatch('catalog_product_compare_add_product', ['product' => $product]);
            }

            $this->compareHelper->calculate();
        }

        return $resultRedirect->setRefererOrBaseUrl();
    }
}
