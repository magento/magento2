<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Category;

use \Magento\CatalogGraphQl\Model\Resolver\Category\DataProvider\Breadcrumbs as BreadcrumbsDataProvider;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\Resolver\ValueFactory;

/**
 * Retrieves breadcrumbs
 */
class Breadcrumbs implements ResolverInterface
{
    /**
     * @var BreadcrumbsDataProvider
     */
    private $breadcrumbsDataProvider;

    /**
     * @var ValueFactory
     */
    private $valueFactory;

    /**
     * @param BreadcrumbsDataProvider $breadcrumbsDataProvider
     * @param ValueFactory $valueFactory
     */
    public function __construct(
        BreadcrumbsDataProvider $breadcrumbsDataProvider,
        ValueFactory $valueFactory
    ) {
        $this->breadcrumbsDataProvider = $breadcrumbsDataProvider;
        $this->valueFactory = $valueFactory;
    }

    /**
     * @inheritdoc
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null): Value
    {
        if (!isset($value['path'])) {
            $result = function () {
                return null;
            };
            return $this->valueFactory->create($result);
        }

        $result = function () use ($value) {
            $breadcrumbsData = $this->breadcrumbsDataProvider->getData($value['path']);
            return count($breadcrumbsData) ? $breadcrumbsData : null;
        };
        return $this->valueFactory->create($result);
    }
}
