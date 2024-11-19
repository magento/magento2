<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Swatches\Controller\Ajax;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json as ResultJson;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\PageCache\Model\Config;
use Magento\PageCache\Model\Config as PageCacheConfig;
use Magento\Swatches\Helper\Data;
use Magento\Swatches\Helper\Data as SwatchHelper;

/**
 * Provide product media data.
 */
class Media extends Action implements HttpGetActionInterface
{
    /**
     * @param Context $context
     * @param ProductFactory $productModelFactory
     * @param SwatchHelper $swatchHelper
     * @param PageCacheConfig $config
     */
    public function __construct(
        Context $context,
        protected readonly ProductFactory $productModelFactory,
        private readonly Data $swatchHelper,
        protected readonly Config $config
    ) {

        parent::__construct($context);
    }

    /**
     * Get product media for specified configurable product variation
     *
     * @return string
     * @throws LocalizedException
     */
    public function execute()
    {
        $productMedia = [];

        /** @var ResultJson $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        /** @var ResponseInterface $response */
        $response = $this->getResponse();

        if ($productId = (int)$this->getRequest()->getParam('product_id')) {
            /** @var ProductInterface $product */
            $product = $this->productModelFactory->create()->load($productId);
            $productMedia = [];
            if ($product->getId() && $product->getStatus() == Status::STATUS_ENABLED) {
                $productMedia = $this->swatchHelper->getProductMediaGallery($product);
            }
            $resultJson->setHeader('X-Magento-Tags', implode(',', $product->getIdentities()));

            $response->setPublicHeaders($this->config->getTtl());
        }
        $resultJson->setData($productMedia);

        return $resultJson;
    }
}
