<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Plugin\Product\Gallery\Processor;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Gallery\Processor;

/**
 * This is a plugin to \Magento\Catalog\Model\Product\Gallery\Processor.
 * It is to set product media gallery changed when such change happens.
 */
class SetProductMediaGalleryChanged
{
    /**
     * Set product media gallery changed after add image to the product
     *
     * @param Processor $subject
     * @param callable $proceed
     * @param Product $product
     * @param string $file
     * @param string|string[] $mediaAttribute
     * @param boolean $move
     * @param boolean $exclude
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundAddImage(
        Processor $subject,
        callable $proceed,
        Product $product,
        $file,
        $mediaAttribute = null,
        $move = false,
        $exclude = true
    ): string {
        $fileName = $proceed($product, $file, $mediaAttribute, $move, $exclude);
        $this->setProductMediaGalleryChanged($product);

        return $fileName;
    }

    /**
     * Set product media gallery changed after update image of the product
     *
     * @param Processor $subject
     * @param callable $proceed
     * @param Product $product
     * @param string $file
     * @param array $data
     * @return Processor
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundUpdateImage(
        Processor $subject,
        callable $proceed,
        Product $product,
        $file,
        $data
    ): Processor {
        $returnValue = $proceed($product, $file, $data);
        $this->setProductMediaGalleryChanged($product);

        return $returnValue;
    }

    /**
     * Set product media gallery changed after remove image from the product
     *
     * @param Processor $subject
     * @param callable $proceed
     * @param Product $product
     * @param string $file
     * @return Processor
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundRemoveImage(
        Processor $subject,
        callable $proceed,
        Product $product,
        $file
    ): Processor {
        $returnValue = $proceed($product, $file);
        $this->setProductMediaGalleryChanged($product);

        return $returnValue;
    }

    /**
     * Set product media gallery changed after set media attribute of the product
     *
     * @param Processor $subject
     * @param callable $proceed
     * @param Product $product
     * @param string|string[] $mediaAttribute
     * @param string $value
     * @return Processor
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundSetMediaAttribute(
        Processor $subject,
        callable $proceed,
        Product $product,
        $mediaAttribute,
        $value
    ): Processor {
        $returnValue = $proceed($product, $mediaAttribute, $value);
        $this->setProductMediaGalleryChanged($product);

        return $returnValue;
    }

    /**
     * Set product media gallery is changed.
     *
     * @param Product $product
     * @return void
     */
    private function setProductMediaGalleryChanged(Product $product): void
    {
        $product->setData('is_media_gallery_changed', true);
    }
}
