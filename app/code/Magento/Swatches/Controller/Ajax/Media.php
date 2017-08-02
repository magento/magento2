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
 * @since 2.0.0
 */
class Media extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Catalog\Model\Product Factory
     * @since 2.0.0
     */
    protected $productModelFactory;

    /**
     * @var \Magento\Swatches\Helper\Data
     * @since 2.0.0
     */
    private $swatchHelper;

    /**
     * @param Context $context
     * @param \Magento\Catalog\Model\ProductFactory $productModelFactory
     * @param \Magento\Swatches\Helper\Data $swatchHelper
     * @since 2.0.0
     */
    public function __construct(
        Context $context,
        \Magento\Catalog\Model\ProductFactory $productModelFactory,
        \Magento\Swatches\Helper\Data $swatchHelper
    ) {
        $this->productModelFactory = $productModelFactory;
        $this->swatchHelper = $swatchHelper;

        parent::__construct($context);
    }

    /**
     * Get product media for specified configurable product variation
     *
     * @return string
     * @since 2.0.0
     */
    public function execute()
    {
        $productMedia = [];
        if ($productId = (int)$this->getRequest()->getParam('product_id')) {
            $productMedia = $this->swatchHelper->getProductMediaGallery(
                $this->productModelFactory->create()->load($productId)
            );
        }

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($productMedia);
        return $resultJson;
    }
}
