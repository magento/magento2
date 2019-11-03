<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\GraphQl\Schema\Type\Output\ElementMapper;

use Magento\Framework\GraphQl\Config\Element\TypeInterface;
use Magento\Framework\GraphQl\Schema\Type\OutputTypeInterface;

/**
 * {@inheritdoc}
 */
class FormatterComposite implements FormatterInterface
{
    /**
     * @var FormatterInterface[]
     */
    private $formatters;

    /**
     * @param FormatterInterface[] $formatters
     */
    public function __construct(array $formatters)
    {
        $this->formatters = $formatters;
    }

    /**
     * {@inheritDoc}
     */
    public function format(TypeInterface $configElement, OutputTypeInterface $outputType) : array
    {
        $config = [
            'name' => $configElement->getName(),
            'description' => $configElement->getDescription()
        ];
        foreach ($this->formatters as $formatter) {
            $config = array_merge($config, $formatter->format($configElement, $outputType));
        }

        return $config;
    }
}
