<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\GraphQl\Config\Converter\Type;

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
     * {@inheritdoc}
     */
    public function format(array $entry) : array
    {
        $formattedEntry = [];
        foreach ($this->formatters as $formatter) {
            $formattedEntry = array_merge_recursive($formattedEntry, $formatter->format($entry));
        }

        return $formattedEntry;
    }
}
