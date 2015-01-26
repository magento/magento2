<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Checkout\Service\V1\Data;

use Magento\Framework\Api\AbstractSearchResultsBuilder;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Api\MetadataServiceInterface;
use Magento\Framework\Api\ObjectFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * @codeCoverageIgnore
 */
class CartSearchResultsBuilder extends AbstractSearchResultsBuilder
{
    /**
     * Constructor
     *
     * @param ObjectFactory $objectFactory
     * @param AttributeValueFactory $valueFactory
     * @param MetadataServiceInterface $metadataService
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param CartBuilder $itemObjectBuilder
     */
    public function __construct(
        ObjectFactory $objectFactory,
        AttributeValueFactory $valueFactory,
        MetadataServiceInterface $metadataService,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CartBuilder $itemObjectBuilder
    ) {
        parent::__construct(
            $objectFactory,
            $valueFactory,
            $metadataService,
            $searchCriteriaBuilder,
            $itemObjectBuilder
        );
    }

    /**
     * Set cart list
     *
     * @param \Magento\Checkout\Service\V1\Data\Cart[] $items
     * @return $this
     */
    public function setItems($items)
    {
        return parent::setItems($items);
    }
}
