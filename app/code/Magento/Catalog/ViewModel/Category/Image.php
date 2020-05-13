<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\ViewModel\Category;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Category\Image as CategoryImage;
use Magento\Framework\View\Element\Block\ArgumentInterface;

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

    /**
     * Initialize dependencies.
     *
     * @param CategoryImage $image
     */
    public function __construct(CategoryImage $image)
    {
        $this->image = $image;
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
}
