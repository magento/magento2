<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Model\Product\Website;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Product\Website\Link as ProductWebsiteLink;
use Magento\Framework\EntityManager\Operation\ExtensionInterface;

/**
 * Class \Magento\Catalog\Model\Product\Website\ReadHandler
 *
 * @since 2.2.0
 */
class ReadHandler implements ExtensionInterface
{
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\Website\Link
     * @since 2.2.0
     */
    private $productWebsiteLink;

    /**
     * ReadHandler constructor.
     * @param ProductWebsiteLink $resourceModel
     * @since 2.2.0
     */
    public function __construct(
        ProductWebsiteLink $productWebsiteLink
    ) {
        $this->productWebsiteLink = $productWebsiteLink;
    }

    /**
     * @param ProductInterface $product
     * @param array $arguments
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @return ProductInterface
     * @since 2.2.0
     */
    public function execute($product, $arguments = [])
    {
        if ($product->getExtensionAttributes()->getWebsiteIds() !== null) {
            return $product;
        }
        $websiteIds = $this->productWebsiteLink->getWebsiteIdsByProductId($product->getId());

        $extensionAttributes = $product->getExtensionAttributes();
        $extensionAttributes->setWebsiteIds($websiteIds);
        $product->setExtensionAttributes($extensionAttributes);

        return $product;
    }
}
