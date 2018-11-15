<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Product\Related;


use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Related\RelatedDataProvider;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;


/**
 * Class RelatedProducts
 * @package Magento\CatalogGraphQl\Model\Resolver\Product\Related
 */
class RelatedProducts implements ResolverInterface
{

    /**
     * Attribute to select fields
     */
    public const FIELDS = ['sku', 'name', 'price', 'image', 'url_path', 'url_key'];
    /**
     * @var RelatedDataProvider
     */
    private $dataProvider;

    /**
     * RelatedProducts constructor.
     * @param RelatedDataProvider $dataProvider
     */
    public function __construct(RelatedDataProvider $dataProvider)
    {
        $this->dataProvider = $dataProvider;
    }

    /**
     * Fetches the data from persistence models and format it according to the GraphQL schema.
     *
     * @param \Magento\Framework\GraphQl\Config\Element\Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @throws \Exception
     * @return mixed|Value
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $product = $value['model'];
        $this->dataProvider->addFieldToSelect(self::FIELDS);
        $collection = $this->dataProvider->getData($product);

        $count = 0;
        $data = [];
        foreach ($collection as $item) {
            $data[$count] = $item->getData();
            $data[$count]['model'] = $item;
            $count++;
        }
        return $data;
    }

}