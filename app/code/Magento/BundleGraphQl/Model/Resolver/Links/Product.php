<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\BundleGraphQl\Model\Resolver\Links;

use GraphQL\Type\Definition\ResolveInfo;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product\FormatterInterface;
use Magento\Framework\GraphQl\Config\Data\Field;
use Magento\Framework\GraphQl\Resolver\ResolverInterface;
use Magento\Framework\GraphQl\Resolver\Value;
use Magento\Framework\GraphQl\Resolver\ValueFactory;

/**
 * {@inheritdoc}
 */
class Product implements ResolverInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var FormatterInterface
     */
    private $formatter;

    /**
     * @var ValueFactory
     */
    private $valueFactory;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param FormatterInterface $formatter
     * @param ValueFactory $valueFactory
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        FormatterInterface $formatter,
        ValueFactory $valueFactory
    ) {
        $this->productRepository = $productRepository;
        $this->formatter = $formatter;
        $this->valueFactory = $valueFactory;
    }

    /**
     * {@inheritDoc}
     */
    public function resolve(Field $field, array $value = null, array $args = null, $context, ResolveInfo $info) : ?Value
    {
        /** @var \Magento\Catalog\Model\Product $product */
        $product = $this->productRepository->get($value['sku']);
        $data = $this->formatter->format($product);

        $result = function () use ($data) {
            return $data;
        };

        return $this->valueFactory->create($result);
    }
}
