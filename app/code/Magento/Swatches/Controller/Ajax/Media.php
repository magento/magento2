<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Swatches\Controller\Ajax;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\PageCache\Model\Config as PageCacheConfig;
use Magento\Swatches\Helper\Data as SwatchesHelper;

class Media extends Action implements HttpGetActionInterface
{
    /**
     * @deprecated Products should be loaded using Repository
     * @var ProductFactory
     */
    protected $productModelFactory;

    /**
     * @var SwatchesHelper
     */
    private $swatchHelper;

    /**
     * @var PageCacheConfig
     */
    protected $config;

    /**
     * @var ProductRepositoryInterface|null
     */
    private $productRepository;

    /**
     * @param Context $context
     * @param ProductFactory $productModelFactory
     * @param SwatchesHelper $swatchHelper
     * @param PageCacheConfig $config
     * @param ProductRepositoryInterface|null $productRepository
     */
    public function __construct(
        Context $context,
        ProductFactory $productModelFactory,
        SwatchesHelper $swatchHelper,
        PageCacheConfig $config,
        ProductRepositoryInterface $productRepository = null
    ) {
        $this->productModelFactory = $productModelFactory;
        $this->swatchHelper = $swatchHelper;
        $this->config = $config;
        $this->productRepository = $productRepository
            ?? ObjectManager::getInstance()->get(ProductRepositoryInterface::class);

        parent::__construct($context);
    }

    /**
     * Get product media for specified configurable product variation
     *
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        try {
            /** @var ProductInterface|Product $product */
            $product = $this->getProductRequested();
            $productMedia = $this->swatchHelper->getProductMediaGallery($product);

            $resultJson->setHeader('X-Magento-Tags', implode(',', $product->getIdentities()));

            /** @var \Magento\Framework\App\ResponseInterface $response */
            $response = $this->getResponse();
            $response->setPublicHeaders($this->config->getTtl());
        } catch (NoSuchEntityException $e) {
            $productMedia = [];
        }

        $resultJson->setData($productMedia);
        return $resultJson;
    }

    /**
     * Returns the requested Product
     *
     * @return ProductInterface
     * @throws NoSuchEntityException
     */
    private function getProductRequested(): ProductInterface
    {
        $productId = (int)$this->_request->getParam('product_id');
        return $this->productRepository->get($productId);
    }
}
