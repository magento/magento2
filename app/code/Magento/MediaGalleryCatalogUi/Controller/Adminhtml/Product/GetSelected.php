<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\MediaGalleryCatalogUi\Controller\Adminhtml\Product;

use Magento\Framework\Controller\ResultInterface;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Backend\App\Action;

/**
 * Returns selected product by product id. for ui-select filter
 */
class GetSelected extends Action implements HttpGetActionInterface
{
    /**
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Catalog::products';

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     *  GetSelected constructor.
     *
     * @param JsonFactory $jsonFactory
     * @param ProductRepositoryInterface $productRepository
     * @param Context $context
     */
    public function __construct(
        JsonFactory $jsonFactory,
        ProductRepositoryInterface $productRepository,
        Context $context
    ) {
        $this->resultJsonFactory = $jsonFactory;
        $this->productRepository = $productRepository;
        parent::__construct($context);
    }

    /**
     * Return selected products options
     *
     * @return ResultInterface
     */
    public function execute() : ResultInterface
    {
        $productIds = $this->getRequest()->getParam('ids');
        $options = [];

        if (!is_array($productIds)) {
            return $this->resultJsonFactory->create()->setData('parameter ids must be type of array');
        }
        foreach ($productIds as $id) {
            try {
                $product = $this->productRepository->getById($id);
                $options[] = [
                    'value' => $product->getId(),
                    'label' => $product->getName(),
                    'is_active' => $product->getSatus(),
                    'path' => $product->getSku()
                ];
            } catch (\Exception $e) {
                continue;
            }
        }

        return $this->resultJsonFactory->create()->setData($options);
    }
}
