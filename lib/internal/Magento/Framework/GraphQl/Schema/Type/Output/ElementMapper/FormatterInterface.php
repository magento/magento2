<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Schema\Type\Output\ElementMapper;

use Magento\Framework\GraphQl\Config\ConfigElementInterface;
use Magento\Framework\GraphQl\Schema\Type\OutputTypeInterface;

/**
 * Converter of GraphQL config elements to the objects compatible with GraphQL schema generator.
 *
 * @api
 */
interface FormatterInterface
{
    /**
     * Convert GraphQL config element to the object compatible with GraphQL schema generator.
     *
     * @param ConfigElementInterface $configElement
     * @param OutputTypeInterface $outputType
     * @return array
     */
    public function format(ConfigElementInterface $configElement, OutputTypeInterface $outputType): array;
}
