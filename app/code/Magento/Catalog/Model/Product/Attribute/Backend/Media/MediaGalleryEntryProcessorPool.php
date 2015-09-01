<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Catalog\Model\Product\Attribute\Backend\Media;

use Magento\Catalog\Model\Product;
use \Magento\Eav\Model\Entity\Attribute\AbstractAttribute;

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
     * @param AbstractAttribute $attribute
     * @return void
     */
    public function processBeforeLoad(Product $product, AbstractAttribute $attribute)
    {
        $this->processAction('beforeLoad', $product, $attribute);
    }

    /**
     * @param Product $product
     * @param AbstractAttribute $attribute
     * @return void
     */
    public function processAfterLoad(Product $product, AbstractAttribute $attribute)
    {
        $this->processAction('afterLoad', $product, $attribute);
    }

    /**
     * @param Product $product
     * @param AbstractAttribute $attribute
     * @return void
     */
    public function processBeforeSave(Product $product, AbstractAttribute $attribute)
    {
        $this->processAction('beforeSave', $product, $attribute);
    }

    /**
     * @param Product $product
     * @param AbstractAttribute $attribute
     * @return void
     */
    public function processAfterSave(Product $product, AbstractAttribute $attribute)
    {
        $this->processAction('afterSave', $product, $attribute);
    }

    /**
     * @param Product $product
     * @param AbstractAttribute $attribute
     * @return void
     */
    public function processBeforeDelete(Product $product, AbstractAttribute $attribute)
    {
        $this->processAction('beforeDelete', $product, $attribute);
    }

    /**
     * @param Product $product
     * @param AbstractAttribute $attribute
     * @return void
     */
    public function processAfterDelete(Product $product, AbstractAttribute $attribute)
    {
        $this->processAction('afterDelete', $product, $attribute);
    }

    /**
     * @param $actionName
     * @param Product $product
     * @param AbstractAttribute $attribute
     * @return void
     */
    protected function processAction($actionName, Product $product, AbstractAttribute $attribute)
    {
        foreach ($this->mediaGalleryEntryProcessorsCollection as $processor) {
            $processor->$actionName($product, $attribute);
        }
    }
}
