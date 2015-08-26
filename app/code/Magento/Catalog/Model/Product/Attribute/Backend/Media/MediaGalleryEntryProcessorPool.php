<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product\Attribute\Backend\Media;

use Magento\Catalog\Model\Product;

/**
 * Class aggregate all Media Gallery Entry Converters
 */
class MediaGalleryEntryProcessorPool
{
    /**
     * @var AbstractMediaGalleryEntryProcessor[]
     */
    private $mediaGalleryEntryProcessorsCollection;

    /**
     * @param array $mediaGalleryEntryProcessorsCollection
     */
    public function __construct(array $mediaGalleryEntryProcessorsCollection)
    {
        foreach ($mediaGalleryEntryProcessorsCollection as $processor) {
            if (!$processor instanceof AbstractMediaGalleryEntryProcessor) {
                throw new \InvalidArgumentException(
                    __('Media Gallery processor should be an instance of AbstractMediaGalleryEntryProcessor.')
                );
            }
        }
        ksort($mediaGalleryEntryProcessorsCollection);
        $this->mediaGalleryEntryProcessorsCollection = $mediaGalleryEntryProcessorsCollection;
    }

    /**
     * @param Product $product
     * @param $attributeCode
     * @return void
     */
    public function processAfterLoad(Product $product, $attributeCode)
    {
        $this->processAction('afterLoad', $product, $attributeCode);
    }

    /**
     * @param Product $product
     * @param $attributeCode
     * @return void
     */
    public function processBeforeSave(Product $product, $attributeCode)
    {
        $this->processAction('beforeSave', $product, $attributeCode);
    }

    /**
     * @param Product $product
     * @param $attributeCode
     * @return void
     */
    public function processAfterSave(Product $product, $attributeCode)
    {
        $this->processAction('afterSave', $product, $attributeCode);
    }

    /**
     * @param string $actionName
     * @param Product $product
     * @param $attributeCode
     * @return void
     */
    protected function processAction($actionName, Product $product, $attributeCode)
    {
        foreach ($this->mediaGalleryEntryProcessorsCollection as $processor) {
            $processor->$actionName($product, $attributeCode);
        }
    }
}
