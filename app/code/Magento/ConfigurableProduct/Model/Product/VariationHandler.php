<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\ConfigurableProduct\Model\Product;

use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Gallery\Processor;
use Magento\Catalog\Model\ProductFactory;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\Eav\Model\Entity\Attribute\SetFactory;
use Magento\Eav\Model\EntityFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\ConfigurableProduct\Model\Product\ChildrenWeightResolver;

/**
 * Variation Handler
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 *
 * @api
 * @since 100.0.2
 */
class VariationHandler
{
    /**
     * @var \Magento\Catalog\Model\Product\Gallery\Processor
     * @since 100.1.0
     */
    protected $mediaGalleryProcessor;

    /**
     * @var \Magento\ConfigurableProduct\Model\Product\Type\Configurable
     */
    protected $configurableProduct;

    /**
     * @var \Magento\Eav\Model\Entity\Attribute\SetFactory
     */
    protected $attributeSetFactory;

    /**
     * @var \Magento\Eav\Model\EntityFactory
     */
    protected $entityFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var \Magento\Eav\Model\Entity\Attribute\AbstractAttribute[]
     */
    private $attributes;

    /**
     * @var \Magento\ConfigurableProduct\Model\Product\ChildrenWeightResolver
     */
    private $childrenWeightResolver;

    /**
     * @var \Magento\CatalogInventory\Api\StockConfigurationInterface
     * @deprecated 100.1.0
     */
    protected $stockConfiguration;

    /**
     * @param Type\Configurable           $configurableProduct
     * @param SetFactory                  $attributeSetFactory
     * @param EntityFactory               $entityFactory
     * @param ProductFactory              $productFactory
     * @param StockConfigurationInterface $stockConfiguration
     * @param Processor                   $mediaGalleryProcessor
     * @param ChildrenWeightResolver      $childrenWeightResolver
     */
    public function __construct(
        Type\Configurable $configurableProduct,
        SetFactory $attributeSetFactory,
        EntityFactory $entityFactory,
        ProductFactory $productFactory,
        StockConfigurationInterface $stockConfiguration,
        Processor $mediaGalleryProcessor,
        ChildrenWeightResolver $childrenWeightResolver

    ) {
        $this->configurableProduct    = $configurableProduct;
        $this->attributeSetFactory    = $attributeSetFactory;
        $this->entityFactory          = $entityFactory;
        $this->productFactory         = $productFactory;
        $this->stockConfiguration     = $stockConfiguration;
        $this->mediaGalleryProcessor  = $mediaGalleryProcessor;
        $this->childrenWeightResolver = $childrenWeightResolver;
    }

    /**
     * Generate simple products to link with configurable
     *
     * @param \Magento\Catalog\Model\Product $parentProduct
     * @param array                          $productsData
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function generateSimpleProducts($parentProduct, $productsData)
    {
        $generatedProductIds = [];
        $this->attributes    = null;
        $productsData        = $this->duplicateImagesForVariations($productsData);
        foreach ($productsData as $simpleProductData) {
            $newSimpleProduct = $this->productFactory->create();
            if (isset($simpleProductData['configurable_attribute'])) {
                $configurableAttribute = json_decode($simpleProductData['configurable_attribute'], true);
                unset($simpleProductData['configurable_attribute']);
            } else {
                throw new LocalizedException(__('Configuration must have specified attributes'));
            }

            $this->fillSimpleProductData(
                $newSimpleProduct,
                $parentProduct,
                array_merge($simpleProductData, $configurableAttribute)
            );
            $newSimpleProduct->save();

            $generatedProductIds[] = $newSimpleProduct->getId();
        }

        return $generatedProductIds;
    }

    /**
     * Prepare attribute set comprising all selected configurable attributes
     *
     * @deprecated 100.1.0
     *
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return void
     */
    protected function prepareAttributeSetToBeBaseForNewVariations(Product $product)
    {
        $this->prepareAttributeSet($product);
    }

    /**
     * Prepare attribute set comprising all selected configurable attributes
     *
     * @param \Magento\Catalog\Model\Product $product
     *
     * @return void
     * @since 100.1.0
     */
    public function prepareAttributeSet(Product $product)
    {
        $attributes     = $this->configurableProduct->getUsedProductAttributes($product);
        $attributeSetId = $product->getNewVariationsAttributeSetId();
        /** @var $attributeSet \Magento\Eav\Model\Entity\Attribute\Set */
        $attributeSet = $this->attributeSetFactory->create()->load($attributeSetId);
        $attributeSet->addSetInfo(
            $this->entityFactory->create()->setType(Product::ENTITY)->getTypeId(),
            $attributes
        );
        foreach ($attributes as $attribute) {
            /* @var $attribute \Magento\Catalog\Model\Entity\Attribute */
            if (!$attribute->isInSet($attributeSetId)) {
                $attribute->setAttributeSetId(
                    $attributeSetId
                )->setAttributeGroupId(
                    $attributeSet->getDefaultGroupId($attributeSetId)
                )->save();
            }
        }
    }

    /**
     * Fill simple product data during generation
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Catalog\Model\Product $parentProduct
     * @param array                          $postData
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     *
     * @return void
     */
    protected function fillSimpleProductData(
        Product $product,
        Product $parentProduct,
        $postData
    ) {

        $product->setStoreId(
            \Magento\Store\Model\Store::DEFAULT_STORE_ID
        )->setTypeId(
            $this->childrenWeightResolver->processProduct($postData)
        )->setAttributeSetId(
            $parentProduct->getNewVariationsAttributeSetId()
        );

        if ($this->attributes === null) {
            $this->attributes = $product->getTypeInstance()->getSetAttributes($product);
        }
        foreach ($this->attributes as $attribute) {
            if ($attribute->getIsUnique() ||
                $attribute->getAttributeCode() == 'url_key' ||
                $attribute->getFrontend()->getInputType() == 'gallery' ||
                $attribute->getFrontend()->getInputType() == 'media_image' ||
                !$attribute->getIsVisible()
            ) {
                continue;
            }

            $product->setData($attribute->getAttributeCode(), $parentProduct->getData($attribute->getAttributeCode()));
        }

        $keysFilter             = ['item_id', 'product_id', 'stock_id', 'type_id', 'website_id'];
        $postData['stock_data'] = array_diff_key((array)$parentProduct->getStockData(), array_flip($keysFilter));
        if (!isset($postData['stock_data']['is_in_stock'])) {
            $stockStatus                           = $parentProduct->getQuantityAndStockStatus();
            $postData['stock_data']['is_in_stock'] = $stockStatus['is_in_stock'];
        }
        $postData           = $this->processMediaGallery($product, $postData);
        $postData['status'] = isset($postData['status'])
            ? $postData['status']
            : \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED;
        $product->addData(
            $postData
        )->setWebsiteIds(
            $parentProduct->getWebsiteIds()
        )->setVisibility(
            \Magento\Catalog\Model\Product\Visibility::VISIBILITY_NOT_VISIBLE
        );
    }

    /**
     * Duplicate images for variations
     *
     * @param array $productsData
     *
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function duplicateImagesForVariations($productsData)
    {
        $imagesForCopy = [];
        foreach ($productsData as $variationId => $simpleProductData) {
            if (!isset($simpleProductData['media_gallery']['images'])) {
                continue;
            }

            foreach ($simpleProductData['media_gallery']['images'] as $imageId => $image) {
                $image['variation_id'] = $variationId;
                if (isset($imagesForCopy[$imageId][0])) {
                    // skip duplicate image for first product
                    unset($imagesForCopy[$imageId][0]);
                }
                $imagesForCopy[$imageId][] = $image;
            }
        }
        foreach ($imagesForCopy as $imageId => $variationImages) {
            foreach ($variationImages as $image) {
                $file                                                                    = $image['file'];
                $variationId                                                             = $image['variation_id'];
                $newFile                                                                 = $this->mediaGalleryProcessor->duplicateImageFromTmp($file);
                $productsData[$variationId]['media_gallery']['images'][$imageId]['file'] = $newFile;
                foreach ($this->mediaGalleryProcessor->getMediaAttributeCodes() as $attribute) {
                    if (isset($productsData[$variationId][$attribute])
                        && $productsData[$variationId][$attribute] == $file
                    ) {
                        $productsData[$variationId][$attribute] = $newFile;
                    }
                }
            }
        }

        return $productsData;
    }

    /**
     * @param Product $product
     * @param array   $productData
     *
     * @return array
     */
    public function processMediaGallery($product, $productData)
    {
        if (!empty($productData['image'])) {
            $image = $productData['image'];
            if (!isset($productData['media_gallery']['images'])) {
                $productData['media_gallery']['images'] = [];
            }
            if (false === array_search($image, array_column($productData['media_gallery']['images'], 'file'))) {
                $productData['small_image']               = $productData['thumbnail'] = $image;
                $productData['media_gallery']['images'][] = [
                    'position' => 1,
                    'file'     => $image,
                    'disabled' => 0,
                    'label'    => '',
                ];
            }
        }
        if ($product->getMediaGallery('images') && !empty($productData['media_gallery']['images'])) {
            $gallery                                = array_map(
                function ($image) {
                    $image['removed'] = 1;

                    return $image;
                },
                $product->getMediaGallery('images')
            );
            $gallery                                = array_merge($productData['media_gallery']['images'], $gallery);
            $productData['media_gallery']['images'] = $gallery;
        }

        return $productData;
    }
}
