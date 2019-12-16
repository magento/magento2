<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Category;

use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Framework\Filesystem\DirectoryList;

/**
 * Resolve category image to a fully qualified URL
 */
class Image implements ResolverInterface
{
    /** @var DirectoryList  */
    private $directoryList;

    /**
     * @param DirectoryList $directoryList
     */
    public function __construct(DirectoryList $directoryList)
    {
        $this->directoryList = $directoryList;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }
        /** @var \Magento\Catalog\Model\Category $category */
        $category = $value['model'];
        $imagePath = $category->getImage();
        if (empty($imagePath)) {
            return null;
        }
        /** @var StoreInterface $store */
        $store = $context->getExtensionAttributes()->getStore();
        $baseUrl = $store->getBaseUrl('media');

        $mediaPath = $this->directoryList->getUrlPath('media');
        $pos = strpos($imagePath, $mediaPath);
        if ($pos !== false) {
            $imagePath = substr($imagePath, $pos + strlen($mediaPath), strlen($baseUrl));
        }
        $imageUrl = rtrim($baseUrl, '/') . '/' . ltrim($imagePath, '/');

        return $imageUrl;
    }
}
