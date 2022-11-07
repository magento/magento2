<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Query\Resolver;

use Magento\Framework\GraphQl\Exception\GraphQlInputException;

/**
 * Composite processor class for arguments
 */
class ArgumentsCompositeProcessor implements ArgumentsProcessorInterface
{
    /**
     * @var ArgumentsProcessorInterface[]
     */
    private $processors = [];

    /**
     * @param ArgumentsProcessorInterface[] $processors
     */
    public function __construct(array $processors = [])
    {
        $this->processors = $processors;
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
        $processedArgs = $args;
        foreach ($this->processors as $processor) {
            $processedArgs = $processor->process(
                $fieldName,
                $processedArgs
            );
        }

        return $processedArgs;
    }
}
