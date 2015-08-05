<?php
/**
 * Product initialzation helper
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Controller\Adminhtml\Product\Initialization\Helper\Plugin;

class UpdateConfigurations
{
    /** @var \Magento\Catalog\Api\ProductRepositoryInterface  */
    protected $productRepository;

    /** @var \Magento\Framework\App\RequestInterface */
    protected $request;

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
    ) {
        $this->request = $request;
        $this->productRepository = $productRepository;
    }

    /**
     * Update data for configurable product configurations
     *
     * @param \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper $subject
     * @param \Magento\Catalog\Model\Product $configurableProduct
     *
     * @return \Magento\Catalog\Model\Product
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterInitialize(
        \Magento\Catalog\Controller\Adminhtml\Product\Initialization\Helper $subject,
        \Magento\Catalog\Model\Product $configurableProduct
    ) {
        $configurations = $this->request->getParam('configurations', []);
        foreach ($configurations as $productId => $productData) {
            /** @var \Magento\Catalog\Model\Product $product */
            $product = $this->productRepository->getById($productId);
            $product->addData($productData);
            if ($product->hasDataChanges()) {
                $product->save();
            }
        }

        return $configurableProduct;
    }
}
