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
 * @inheritDoc
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
     * @inheritDoc
     */
    public function format(ConfigElementInterface $configElement, OutputTypeInterface $outputType): array
    {
        $defaultConfig = [
            'name' => $configElement->getName(),
            'description' => $configElement->getDescription()
        ];
        $formattedConfig = [];
        foreach ($this->formatters as $formatter) {
            $formattedConfig[] = $formatter->format($configElement, $outputType);
        }

        return array_merge($defaultConfig, ...$formattedConfig);
    }
}
