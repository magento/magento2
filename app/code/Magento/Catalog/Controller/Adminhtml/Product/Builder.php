<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Controller\Adminhtml\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\Cms\Model\Wysiwyg as WysiwygModel;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\StoreFactory;
use Psr\Log\LoggerInterface as Logger;
use Magento\Framework\Registry;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Type as ProductTypes;

/**
 * Build a product based on a request
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Builder
{
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    protected $wysiwygConfig;

    /**
     * @var StoreFactory
     */
    protected $storeFactory;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * Constructor
     *
     * @param ProductFactory $productFactory
     * @param Logger $logger
     * @param Registry $registry
     * @param WysiwygModel\Config $wysiwygConfig
     * @param StoreFactory|null $storeFactory
     * @param ProductRepositoryInterface|null $productRepository
     */
    public function __construct(
        ProductFactory $productFactory,
        Logger $logger,
        Registry $registry,
        WysiwygModel\Config $wysiwygConfig,
        ?StoreFactory $storeFactory = null,
        ?ProductRepositoryInterface $productRepository = null
    ) {
        $this->productFactory = $productFactory;
        $this->logger = $logger;
        $this->registry = $registry;
        $this->wysiwygConfig = $wysiwygConfig;
        $this->storeFactory = $storeFactory ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(StoreFactory::class);
        $this->productRepository = $productRepository ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(ProductRepositoryInterface::class);
    }

    /**
     * Build product based on user request
     *
     * @param RequestInterface $request
     * @return ProductInterface
     * @throws \RuntimeException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function build(RequestInterface $request): ProductInterface
    {
        $productId = (int) $request->getParam('id');
        $storeId = $request->getParam('store', 0);
        $attributeSetId = (int) $request->getParam('set');
        $typeId = $request->getParam('type');

        if ($productId) {
            try {
                $product = $this->productRepository->getById($productId, true, $storeId);
                if ($attributeSetId) {
                    $product->setAttributeSetId($attributeSetId);
                }
            } catch (\Exception $e) {
                $product = $this->createEmptyProduct(ProductTypes::DEFAULT_TYPE, $attributeSetId, $storeId);
                $this->logger->critical($e);
            }
        } else {
            $product = $this->createEmptyProduct($typeId, $attributeSetId, $storeId);
        }

        $store = $this->storeFactory->create();
        $store->load($storeId);

        $this->registry->unregister('product');
        $this->registry->unregister('current_product');
        $this->registry->unregister('current_store');
        $this->registry->register('product', $product);
        $this->registry->register('current_product', $product);
        $this->registry->register('current_store', $store);

        $this->wysiwygConfig->setStoreId($storeId);

        return $product;
    }

    /**
     * Create a product with the given properties
     *
     * @param int $typeId
     * @param int $attributeSetId
     * @param int $storeId
     * @return \Magento\Catalog\Model\Product
     */
    private function createEmptyProduct($typeId, $attributeSetId, $storeId): Product
    {
        /** @var $product \Magento\Catalog\Model\Product */
        $product = $this->productFactory->create();
        $product->setData('_edit_mode', true);

        if ($typeId !== null) {
            $product->setTypeId($typeId);
        }

        if ($storeId !== null) {
            $product->setStoreId($storeId);
        }

        if ($attributeSetId) {
            $product->setAttributeSetId($attributeSetId);
        }

        return $product;
    }
}
