<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Api\SearchCriteria;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Data\Collection\AbstractDb;

class CollectionProcessor implements CollectionProcessorInterface
{
    /**
     * @var CollectionProcessorInterface[]
     */
    private $processors;

    /**
     * @param CollectionProcessorInterface[] $processors
     */
    public function __construct(
        array $processors
    ) {
        $this->processors = $processors;
    }

    /**
     * @inheritDoc
     */
    public function process(SearchCriteriaInterface $searchCriteria, AbstractDb $collection)
    {
        foreach ($this->processors as $name => $processor) {
            if (!($processor instanceof CollectionProcessorInterface)) {
                throw new \InvalidArgumentException(
                    sprintf('Processor %s must implement %s interface.', $name, CollectionProcessorInterface::class)
                );
            }
            $processor->process($searchCriteria, $collection);
        }
    }
}
