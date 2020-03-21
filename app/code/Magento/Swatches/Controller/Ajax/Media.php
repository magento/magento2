<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Swatches\Controller\Ajax;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\Response\Http as HttpResponse;
use Magento\Framework\Controller\Result\JsonFactory as JsonResultFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\PageCache\Model\Config as PageCacheConfig;
use Magento\Swatches\Helper\Data as SwatchesHelper;

/**
 * Get product media for specified configurable product variation
 */
class Media implements HttpGetActionInterface
{
    /**
     * @var SwatchesHelper
     */
    private $swatchHelper;

    /**
     * @var PageCacheConfig
     */
    protected $config;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var JsonResultFactory
     */
    private $jsonResultFactory;

    /**
     * @var HttpResponse
     */
    private $response;

    /**
     * @param RequestInterface $request
     * @param ProductRepositoryInterface $productRepository
     * @param HttpResponse $response
     * @param JsonResultFactory $jsonResultFactory
     * @param SwatchesHelper $swatchHelper
     * @param PageCacheConfig $config
     */
    public function __construct(
        RequestInterface $request,
        ProductRepositoryInterface $productRepository,
        HttpResponse $response,
        JsonResultFactory $jsonResultFactory,
        SwatchesHelper $swatchHelper,
        PageCacheConfig $config
    ) {
        $this->request = $request;
        $this->productRepository = $productRepository;
        $this->response = $response;
        $this->jsonResultFactory = $jsonResultFactory;
        $this->swatchHelper = $swatchHelper;
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $resultJson = $this->jsonResultFactory->create();

        try {
            $product = $this->getCurrentProduct();

            /** @TODO This header should be set by Plugin (bridge between PageCache and Swatches) */
            $this->response->setPublicHeaders($this->config->getTtl());

            $resultJson->setData($this->swatchHelper->getProductMediaGallery($product));
            $resultJson->setHeader('X-Magento-Tags', implode(',', $product->getIdentities()));
            return $resultJson;
        } catch (NoSuchEntityException $e) {
            $resultJson->setData([]);
        }

        return $resultJson;
    }

    /**
     * Returns requested Product
     *
     * @return ProductInterface|Product
     * @throws NoSuchEntityException
     */
    private function getCurrentProduct(): ProductInterface
    {
        $productId = $this->request->getParam('product_id');
        if (empty($productId)) {
            throw new NoSuchEntityException(__('No "product_id" provided.'));
        }

        return $this->productRepository->get($productId);
    }
}
