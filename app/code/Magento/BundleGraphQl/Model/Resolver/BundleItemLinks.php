<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\BundleGraphQl\Model\Resolver;

use GraphQL\Type\Definition\ResolveInfo;
use Magento\Bundle\Model\Selection;
use Magento\Framework\GraphQl\Config\Data\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Resolver\ResolverInterface;
use Magento\Bundle\Model\ResourceModel\Selection\CollectionFactory;
use Magento\Bundle\Model\ResourceModel\Selection\Collection;
use Magento\Framework\GraphQl\Query\EnumLookup;

/**
 * {@inheritdoc}
 */
class BundleItemLinks implements ResolverInterface
{
    /**
     * @var CollectionFactory
     */
    private $linkCollectionFactory;

    /**
     * @var EnumLookup
     */
    private $enumLookup;

    /**
     * @param CollectionFactory $linkCollectionFactory
     * @param EnumLookup $enumLookup
     */
    public function __construct(CollectionFactory $linkCollectionFactory, EnumLookup $enumLookup)
    {
        $this->linkCollectionFactory = $linkCollectionFactory;
        $this->enumLookup = $enumLookup;
    }

    /**
     * @inheritDoc
     */
    public function resolve(Field $field, array $value = null, array $args = null, $context, ResolveInfo $info)
    {
        /** @var Collection $linkCollection */
        $linkCollection = $this->linkCollectionFactory->create();
        $linkCollection->setOptionIdsFilter([$value['option_id']]);
        $field = 'parent_product_id';
        foreach ($linkCollection->getSelect()->getPart('from') as $tableAlias => $data) {
            if ($data['tableName'] == $linkCollection->getTable('catalog_product_bundle_selection')) {
                $field = $tableAlias . '.' . $field;
            }
        }

        $linkCollection->getSelect()
            ->where($field . ' = ?', $value['parent_id']);

        $links = [];
        /** @var Selection $link */
        foreach ($linkCollection as $link) {
            $data = $link->getData();
            $formattedLink = [
                'price' => $link->getSelectionPriceValue(),
                'position' => $link->getPosition(),
                'id' => $link->getId(),
                'qty' => (int)$link->getSelectionQty(),
                'is_default' => (bool)$link->getIsDefault(),
                'price_type' => $this->enumLookup->getEnumValueFromField(
                    'PriceTypeEnum',
                    $link->getSelectionPriceType()
                ) ?: 'DYNAMIC',
                'can_change_quantity' => $link->getSelectionCanChangeQty(),
            ];
            $data = array_replace($data, $formattedLink);
            $data['label'] = function () use ($data) {
                return isset($data['product']) ? $data['product']['name'] : "";
            };
            $links[] = $data;
        }

        return $links;
    }
}
