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
class Media extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Catalog\Model\Product Factory
     */
    protected $productModelFactory;

    /**
     * @var \Magento\Swatches\Model\Product\Variations\Media
     */
    private $variationsMedia;

    /**
     * @param Context $context
     * @param \Magento\Catalog\Model\ProductFactory $productModelFactory
     * @param \Magento\Swatches\Model\Product\Variations\Media $variationsMedia
     */
    public function __construct(
        Context $context,
        \Magento\Catalog\Model\ProductFactory $productModelFactory,
        \Magento\Swatches\Model\Product\Variations\Media $variationsMedia
    ) {
        $this->productModelFactory = $productModelFactory;
        $this->variationsMedia = $variationsMedia;

        parent::__construct($context);
    }

    /**
     * Get product media by fallback:
     * 1stly by default attribute values
     * 2ndly by getting base image from configurable product
     *
     * @return string
     */
    public function execute()
    {
        $productMedia = [];
        if ($productId = (int)$this->getRequest()->getParam('product_id')) {
            $productMedia = $this->variationsMedia->getProductVariationWithMedia(
                $this->productModelFactory->create()->load($productId),
                (array)$this->getRequest()->getParam('attributes'),
                (array)$this->getRequest()->getParam('additional')
            );
        }

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($productMedia);
        return $resultJson;
    }
}
