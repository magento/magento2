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
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\Product\Compare\Item;
use Magento\Catalog\Model\Product\Compare\ItemFactory;
use Magento\Catalog\Model\Product\Compare\ListCompare;
use Magento\Catalog\Model\ResourceModel\Product\Compare\Item\CollectionFactory;
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
 * Remove item from compare list action.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Remove extends Compare implements HttpPostActionInterface
{
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

        $this->compareHelper = $compareHelper;
        $this->escaper = $escaper;
    }

    /**
     * Remove item from compare list.
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @return ResultInterface
     */
    public function execute()
    {
        $productId = (int)$this->getRequest()->getParam('product');
        if ($this->_formKeyValidator->validate($this->getRequest()) && $productId) {
            $storeId = $this->_storeManager->getStore()->getId();
            try {
                /** @var Product $product */
                $product = $this->productRepository->getById($productId, false, $storeId);
            } catch (NoSuchEntityException $e) {
                $product = null;
            }

            if ($product && (int)$product->getStatus() !== Status::STATUS_DISABLED) {
                /** @var $item Item */
                $item = $this->_compareItemFactory->create();
                if ($this->_customerSession->isLoggedIn()) {
                    $item->setCustomerId($this->_customerSession->getCustomerId());
                } elseif ($this->_customerId) {
                    $item->setCustomerId($this->_customerId);
                } else {
                    $item->addVisitorId($this->_customerVisitor->getId());
                }

                $item->loadByProduct($product);
                if ($item->getId()) {
                    $item->delete();
                    $productName = $this->escaper->escapeHtml($product->getName());
                    $this->messageManager->addSuccessMessage(
                        __('You removed product %1 from the comparison list.', $productName)
                    );
                    $this->_eventManager->dispatch(
                        'catalog_product_compare_remove_product',
                        ['product' => $item]
                    );
                    $this->compareHelper->calculate();
                }
            }
        }

        if (!$this->getRequest()->getParam('isAjax', false)) {
            $resultRedirect = $this->resultRedirectFactory->create();

            return $resultRedirect->setRefererOrBaseUrl();
        }

        return null;
    }
}
