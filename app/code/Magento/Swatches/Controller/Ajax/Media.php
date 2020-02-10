<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Swatches\Controller\Ajax;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class Media
 */
class Media extends \Magento\Framework\App\Action\Action implements \Magento\Framework\App\Action\HttpGetActionInterface
{
    /**
     * @var \Magento\Catalog\Model\Product Factory
     */
    protected $productModelFactory;

    /**
     * @var \Magento\Swatches\Helper\Data
     */
    private $swatchHelper;

    /**
     * @var \Magento\PageCache\Model\Config
     */
    protected $config;

    /**
     * @param Context $context
     * @param \Magento\Catalog\Model\ProductFactory $productModelFactory
     * @param \Magento\Swatches\Helper\Data $swatchHelper
     * @param \Magento\PageCache\Model\Config $config
     */
    public function __construct(
        Context $context,
        \Magento\Catalog\Model\ProductFactory $productModelFactory,
        \Magento\Swatches\Helper\Data $swatchHelper,
        \Magento\PageCache\Model\Config $config
    ) {
        $this->productModelFactory = $productModelFactory;
        $this->swatchHelper = $swatchHelper;
        $this->config = $config;

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
        $productMedia = [];

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);

        /** @var \Magento\Framework\App\ResponseInterface $response */
        $response = $this->getResponse();

        if ($productId = (int)$this->getRequest()->getParam('product_id')) {
            $product = $this->productModelFactory->create()->load($productId);
            $productMedia = $this->swatchHelper->getProductMediaGallery(
                $product
            );
            $resultJson->setHeader('X-Magento-Tags', implode(',', $product->getIdentities()));

            $response->setPublicHeaders($this->config->getTtl());
        }

        $resultJson->setData($productMedia);
        return $resultJson;
    }
}
