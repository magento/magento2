<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Product;

use Magento\Catalog\Helper\ImageFactory as CatalogImageHelperFactory;
use Magento\Catalog\Model\Product;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Returns product's  image. If the image is not set, returns a placeholder
 */
class SmallImage implements ResolverInterface
{
    /**
     * @var CatalogImageHelperFactory
     */
    private $catalogImageHelperFactory;

    /**
     * @var ValueFactory
     */
    private $valueFactory;

    /**
     * @param ValueFactory $valueFactory
     * @param CatalogImageHelperFactory $catalogImageHelperFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ValueFactory $valueFactory,
        CatalogImageHelperFactory $catalogImageHelperFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->valueFactory = $valueFactory;
        $this->catalogImageHelperFactory = $catalogImageHelperFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ): Value {
        if (!isset($value['model'])) {
            $result = function () {
                return null;
            };
            return $this->valueFactory->create($result);
        }
        /** @var Product $product */
        $product = $value['model'];
        $imageType = $field->getName();

        $catalogImageHelper = $this->catalogImageHelperFactory->create();
        $imageUrl = $catalogImageHelper->init(
            $product,
            'product_' . $imageType,
            ['type' => $imageType]
        )->getUrl();

        $imageData = [
            'url' => $imageUrl,
            'path' => $product->getData($imageType)
        ];

        $result = function () use ($imageData) {
            return $imageData;
        };

        return $this->valueFactory->create($result);
    }
}
