<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Catalog\Controller\Adminhtml\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\App\Action\HttpGetActionInterface;

/**
 * Controller to search product for ui-select component
 */
class Search extends \Magento\Backend\App\Action implements HttpGetActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Catalog::products';

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var \Magento\Catalog\Model\ProductLink\Search
     */
    private $productSearch;

    /**
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultFactory
     * @param \Magento\Catalog\Model\ProductLink\Search $productSearch
     * @param \Magento\Backend\App\Action\Context $context
     */
    public function __construct(
        \Magento\Framework\Controller\Result\JsonFactory $resultFactory,
        \Magento\Catalog\Model\ProductLink\Search $productSearch,
        \Magento\Backend\App\Action\Context $context
    ) {
        $this->resultJsonFactory = $resultFactory;
        $this->productSearch = $productSearch;
        parent::__construct($context);
    }

    /**
     * Execute product search.
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute() : \Magento\Framework\Controller\ResultInterface
    {
        $searchKey = $this->getRequest()->getParam('searchKey');
        $pageNum = (int)$this->getRequest()->getParam('page');
        $limit = (int)$this->getRequest()->getParam('limit');

        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $productCollection */
        $productCollection = $this->productSearch->prepareCollection($searchKey, $pageNum, $limit);
        $totalValues = $productCollection->getSize();
        $productById = [];
        /** @var  ProductInterface $product */
        foreach ($productCollection as $product) {
            $productId = $product->getId();
            $productById[$productId] = [
                'value' => $productId,
                'label' => $product->getName(),
                'is_active' => $product->getStatus(),
                'path' => $product->getSku(),
                'optgroup' => false
            ];
        }
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();
        return $resultJson->setData([
            'options' => $productById,
            'total' => empty($productById) ? 0 : $totalValues
        ]);
    }
}
