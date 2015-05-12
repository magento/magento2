<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Downloadable\Model\Plugin;

class AfterProductLoad
{
    /**
     * @var \Magento\Downloadable\Api\LinkRepositoryInterface
     */
    protected $linkRepository;

    /**
     * @var \Magento\Catalog\Api\Data\ProductExtensionFactory
     */
    protected $productExtensionFactory;

    /**
     * @param \Magento\Downloadable\Api\LinkRepositoryInterface $linkRepository
     * @param \Magento\Catalog\Api\Data\ProductExtensionFactory $productExtensionFactory
     */
    public function __construct(
        \Magento\Downloadable\Api\LinkRepositoryInterface $linkRepository,
        \Magento\Catalog\Api\Data\ProductExtensionFactory $productExtensionFactory
    ) {
        $this->linkRepository = $linkRepository;
        $this->productExtensionFactory = $productExtensionFactory;
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     * @return \Magento\Catalog\Model\Product
     */
    public function afterLoad(
        \Magento\Catalog\Model\Product $product
    ) {
        if ($product->getTypeId() != \Magento\Downloadable\Model\Product\Type::TYPE_DOWNLOADABLE) {
            return $product;
        }

        $productExtension = $product->getExtensionAttributes();
        if ($productExtension === null) {
            $productExtension = $this->productExtensionFactory->create();
        }
        $links = $this->linkRepository->getLinksByProduct($product);
        if ($links !== null) {
            $productExtension->setDownloadableProductLinks($links);
        }
        $samples = $this->linkRepository->getSamplesByProduct($product);
        if ($samples !== null) {
            $productExtension->setDownloadableProductSamples($samples);
        }

        $product->setExtensionAttributes($productExtension);

        return $product;
    }
}
