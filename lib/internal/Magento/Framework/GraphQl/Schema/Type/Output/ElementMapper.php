<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Schema\Type\Output;

use Magento\Framework\GraphQl\Config\ConfigElementInterface;
use Magento\Framework\GraphQl\Schema\Type\OutputTypeInterface;
use Magento\Framework\GraphQl\Schema\Type\Output\ElementMapper\FormatterInterface;

/**
 * Mapper of config element objects to the objects compatible with GraphQL schema generator.
 */
class ElementMapper
{
    /**
     * @var FormatterInterface
     */
    private $formatter;

    /**
     * @param FormatterInterface $formatter
     */
    public function __construct(FormatterInterface $formatter)
    {
        $this->formatter = $formatter;
    }

    /**
     * Convert provided config element to the object compatible with GraphQL schema generator.
     *
     * @param \Magento\Framework\GraphQl\Config\ConfigElementInterface $configElement
     * @param OutputTypeInterface $outputType
     * @return array
     */
    public function buildSchemaArray(ConfigElementInterface $configElement, OutputTypeInterface $outputType) : array
    {
        return $this->formatter->format($configElement, $outputType);
    }
}
