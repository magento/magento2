<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product\CollectionProcessor;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\CatalogGraphQl\Model\Resolver\Products\DataProvider\Product\CollectionProcessorInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\GraphQl\Model\Query\ContextInterface;

/**
 * Add necessary joins for extensible entities.
 *
 * {@inheritdoc}
 */
class ExtensibleEntityProcessor implements CollectionProcessorInterface
{
    /**
     * @var JoinProcessorInterface
     */
    private $joinProcessor;

    /**
     * @param JoinProcessorInterface $joinProcessor
     */
    public function __construct(JoinProcessorInterface $joinProcessor)
    {
        $this->joinProcessor = $joinProcessor;
    }

    /**
     * @inheritdoc
     */
    public function process(
        Collection $collection,
        SearchCriteriaInterface $searchCriteria,
        array $attributeNames,
        ContextInterface $context
    ): Collection {
        $this->joinProcessor->process($collection);

        return $collection;
    }
}
