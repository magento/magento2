<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Products\Query;

use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ArgumentsProcessorInterface;

/**
 * Category Path processor class for category uid and category id arguments
 */
class CategoryUrlPathArgsProcessor implements ArgumentsProcessorInterface
{
    private const ID = 'category_id';

    private const UID = 'category_uid';

    private const PATH = 'category_url_path';

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(CollectionFactory $collectionFactory)
    {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Composite processor that loops through available processors for arguments that come from graphql input
     *
     * @param string $fieldName,
     * @param array $args
     * @return array
     * @throws GraphQlInputException
     */
    public function process(
        string $fieldName,
        array $args
    ): array {
        $idFilter = $args['filter'][self::ID] ?? [];
        $uidFilter = $args['filter'][self::UID] ?? [];
        $pathFilter = $args['filter'][self::PATH] ?? [];

        if (!empty($pathFilter) && $fieldName === 'products') {
            if (!empty($idFilter)) {
                throw new GraphQlInputException(
                    __('`%1` and `%2` can\'t be used at the same time.', [self::ID, self::PATH])
                );
            } elseif (!empty($uidFilter)) {
                throw new GraphQlInputException(
                    __('`%1` and `%2` can\'t be used at the same time.', [self::UID, self::PATH])
                );
            }

            /** @var Collection $collection */
            $collection = $this->collectionFactory->create();
            $collection->addAttributeToSelect('entity_id');
            $collection->addAttributeToFilter('url_path', $pathFilter['eq']);

            if ($collection->count() === 0) {
                throw new GraphQlInputException(
                    __('No category with the provided `%1` was found', ['category_url_path'])
                );
            }
            $category = $collection->getFirstItem();
            $args['filter'][self::ID]['eq'] = $category->getId();

            unset($args['filter'][self::PATH]);
        }
        return $args;
    }
}
