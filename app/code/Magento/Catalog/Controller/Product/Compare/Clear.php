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
use Magento\Catalog\Model\Product\Compare\ItemFactory;
use Magento\Catalog\Model\Product\Compare\ListCompare;
use Magento\Catalog\Model\ResourceModel\Product\Compare\Item\Collection;
use Magento\Catalog\Model\ResourceModel\Product\Compare\Item\CollectionFactory;
use Magento\Customer\Model\Session;
use Magento\Customer\Model\Visitor;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Remove all items from compare list action.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Clear extends Compare implements HttpPostActionInterface
{
    /**
     * @var CompareHelper
     */
    private $compareHelper;

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
        CompareHelper $compareHelper
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
    }

    /**
     * Remove all items from comparison list
     *
     * @return ResultInterface
     */
    public function execute()
    {
        /** @var Collection $items */
        $items = $this->_itemCollectionFactory->create();

        if ($this->_customerSession->isLoggedIn()) {
            $items->setCustomerId($this->_customerSession->getCustomerId());
        } elseif ($this->_customerId) {
            $items->setCustomerId($this->_customerId);
        } else {
            $items->setVisitorId($this->_customerVisitor->getId());
        }

        try {
            $items->clear();
            $this->messageManager->addSuccessMessage(__('You cleared the comparison list.'));
            $this->compareHelper->calculate();
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Something went wrong  clearing the comparison list.'));
        }

        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setRefererOrBaseUrl();
    }
}
