<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types = 1);

namespace Magento\Framework\GraphQl\Type\Output\ElementMapper;

use Magento\Framework\GraphQl\Config\Element\TypeInterface as TypeElementInterface;
use Magento\Framework\GraphQl\Type\Definition\OutputType;

/**
 * Converter of GraphQL config elements to the objects compatible with GraphQL schema generator.
 */
interface FormatterInterface
{
    /**
     * Convert GraphQL config element to the object compatible with GraphQL schema generator.
     *
     * @param TypeElementInterface $configElement
     * @param OutputType $outputType
     * @return array
     */
    public function format(TypeElementInterface $configElement, OutputType $outputType) : array;
}
