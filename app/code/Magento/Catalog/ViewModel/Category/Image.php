<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\ViewModel\Category;

use Magento\Framework\App\ObjectManager;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Category\Image as CategoryImage;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
//use Magento\Catalog\Model\Product\Image as ProductImage;
use Magento\Catalog\Model\Product as ProductImage;
/**
 * Category image view model
 */
class Image implements ArgumentInterface
{
    private const ATTRIBUTE_NAME = 'image';
    /**
     * @var CategoryImage
     */
    private $image;


    private object $product;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * Initialize dependencies.
     *
     * @param CategoryImage $image
     *
     */
    public function __construct(CategoryImage $image)
    {
        $this->image = $image;
        $this->product = $product ?? ObjectManager::getInstance()->get(ProductImage::class);
    }

    /**
     * Resolve category image URL
     *
     * @param Category $category
     * @param string $attributeCode
     * @return string
     */
    public function getUrl(Category $category, string $attributeCode = self::ATTRIBUTE_NAME): string
    {
        return $this->image->getUrl($category, $attributeCode);
    }

    /**
     * Get unique container ID for image
     * @param $product string
     * @return string
     */
    public function getContainerIdnew( $product) : string
    {
        if (!$this->product->hasData('container_id')) {
            $uniqId = uniqid($product);
            //$uniqId = uniqid($this->product->getProductId());
            $this->product->setData('container_id', $uniqId);
        }

        return $this->product->getData('container_id');
    }
}
