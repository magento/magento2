<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Api\SearchCriteria;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Data\Collection\AbstractDb;

class CollectionProcessorComposite implements CollectionProcessorInterface
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
        foreach ($this->processors as $processor) {
            if (!($processor instanceof CollectionProcessorInterface)) {
                throw new \InvalidArgumentException(
                    sprintf('Processor must implement %s interface.', CollectionProcessorInterface::class)
                );
            }
            $processor->process($searchCriteria, $collection);
        }
    }
}
