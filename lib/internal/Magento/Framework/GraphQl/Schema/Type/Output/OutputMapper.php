<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Schema\Type\Output;

use Magento\Framework\GraphQl\Schema\Type\OutputTypeInterface;
use Magento\Framework\GraphQl\Schema\Type\TypeRegistry;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\Phrase;

/**
 * Map type names to their output type/interface/union/enum classes.
 */
class OutputMapper
{
    /**
     * @var TypeRegistry
     */
    private $typeRegistry;

    /**
     * @param TypeRegistry $typeRegistry
     */
    public function __construct(
        TypeRegistry $typeRegistry
    ) {
        $this->typeRegistry = $typeRegistry;
    }

    /**
     * Get GraphQL output type object by type name.
     *
     * @param string $typeName
     * @return OutputTypeInterface
     * @throws GraphQlInputException
     */
    public function getOutputType(string $typeName)
    {
        $outputType = $this->typeRegistry->get($typeName);

        if (!$outputType instanceof OutputTypeInterface) {
            throw new GraphQlInputException(
                new Phrase("Type '{$typeName}' was requested but is not declared in the GraphQL schema.")
            );
        }
        return $outputType;
    }
}
