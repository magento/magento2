<?php
/**
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Category;

use Magento\Catalog\Model\Category;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Store\Api\Data\StoreInterface;
use Magento\Catalog\Helper\Category as CategoryHelper;

/**
 * Resolve data for category canonical URL
 */
class CanonicalUrl implements ResolverInterface
{
    /** @var CategoryHelper */
    private $categoryHelper;

    /**
     * CanonicalUrl constructor.
     * @param CategoryHelper $categoryHelper
     */
    public function __construct(CategoryHelper $categoryHelper)
    {
        $this->categoryHelper = $categoryHelper;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        ?array $value = null,
        ?array $args = null
    ) {
        if (!isset($value['model'])) {
            throw new LocalizedException(__('"model" value should be specified'));
        }

        /* @var Category $category */
        $category = $value['model'];
        /** @var StoreInterface $store */
        $store = $context->getExtensionAttributes()->getStore();
        if ($this->categoryHelper->canUseCanonicalTag($store)) {
            $baseUrl = $category->getUrlInstance()->getBaseUrl();
            return $category->getUrl() !== null ? str_replace($baseUrl, '', $category->getUrl()) : '';
        }
        return null;
    }
}
