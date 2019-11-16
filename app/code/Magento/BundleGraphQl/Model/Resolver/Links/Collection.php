<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\BundleGraphQl\Model\Resolver\Links;

use Magento\Bundle\Model\Selection;
use Magento\Bundle\Model\ResourceModel\Selection\CollectionFactory;
use Magento\Bundle\Model\ResourceModel\Selection\Collection as LinkCollection;
use Magento\Framework\GraphQl\Query\EnumLookup;

/**
 * Collection to fetch link data at resolution time.
 */
class Collection
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
     * @var int[]
     */
    private $optionIds = [];

    /**
     * @var int[]
     */
    private $parentIds = [];

    /**
     * @var array
     */
    private $links = [];

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
     * Add option and id filter pair to filter for fetch.
     *
     * @param int $optionId
     * @param int $parentId
     * @return void
     */
    public function addIdFilters(int $optionId, int $parentId) : void
    {
        if (!in_array($optionId, $this->optionIds)) {
            $this->optionIds[] = $optionId;
        }
        if (!in_array($parentId, $this->parentIds)) {
            $this->parentIds[] = $parentId;
        }
    }

    /**
     * Retrieve links for passed in option id.
     *
     * @param int $optionId
     * @return array
     */
    public function getLinksForOptionId(int $optionId) : array
    {
        $linksList = $this->fetch();

        if (!isset($linksList[$optionId])) {
            return [];
        }

        return $linksList[$optionId];
    }

    /**
     * Fetch link data and return in array format. Keys for links will be their option Ids.
     *
     * @return array
     */
    private function fetch() : array
    {
        if (empty($this->optionIds) || empty($this->parentIds) || !empty($this->links)) {
            return $this->links;
        }

        /** @var LinkCollection $linkCollection */
        $linkCollection = $this->linkCollectionFactory->create();
        $linkCollection->setOptionIdsFilter($this->optionIds);
        $field = 'parent_product_id';
        foreach ($linkCollection->getSelect()->getPart('from') as $tableAlias => $data) {
            if ($data['tableName'] == $linkCollection->getTable('catalog_product_bundle_selection')) {
                $field = $tableAlias . '.' . $field;
            }
        }

        $linkCollection->getSelect()
            ->where($field . ' IN (?)', $this->parentIds);

        /** @var Selection $link */
        foreach ($linkCollection as $link) {
            $data = $link->getData();
            $formattedLink = [
                'price' => $link->getSelectionPriceValue(),
                'position' => $link->getPosition(),
                'id' => $link->getSelectionId(),
                'qty' => (float)$link->getSelectionQty(),
                'quantity' => (float)$link->getSelectionQty(),
                'is_default' => (bool)$link->getIsDefault(),
                'price_type' => $this->enumLookup->getEnumValueFromField(
                    'PriceTypeEnum',
                    (string)$link->getSelectionPriceType()
                ) ?: 'DYNAMIC',
                'can_change_quantity' => $link->getSelectionCanChangeQty(),
            ];
            $data = array_replace($data, $formattedLink);
            if (!isset($this->links[$link->getOptionId()])) {
                $this->links[$link->getOptionId()] = [];
            }
            $this->links[$link->getOptionId()][] = $data;
        }

        return $this->links;
    }
}
