<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types = 1);

namespace Magento\Framework\GraphQl\Config\Converter\Normalizer;

use Magento\Framework\GraphQl\Config\Converter\Type\FormatterInterface;
use Magento\Framework\GraphQl\Config\Converter\NormalizerInterface;

/**
 * Normalize output and interface types from a configured GraphQL Schema.
 */
class Output implements NormalizerInterface
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
     * {@inheritDoc}
     */
    public function normalize(array $source): array
    {
        foreach ($source as $interface) {
            if ($interface['type'] == 'graphql_interface') {
                foreach ($source as $typeName => $type) {
                    if (isset($type['implements'])
                        && isset($type['implements'][$interface['name']])
                        && isset($type['implements'][$interface['name']]['copyFields'])
                        && $type['implements'][$interface['name']]['copyFields'] === true
                    ) {
                        $source[$typeName]['fields'] = isset($type['fields'])
                            ? array_replace($interface['fields'], $type['fields']) : $interface['fields'];
                    }
                }
            }
        }

        return $source;
    }
}
