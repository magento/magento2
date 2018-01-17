<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

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

        $types = $this->normalizeTypes($source);
        $interfaces = $this->normalizeInterfaces($source);
        foreach ($interfaces as $interface) {
            foreach ($types as $name => $type) {
                if (isset($type['implements'])
                    && isset($type['implements'][$interface['name']])
                    && isset($type['implements'][$interface['name']]['copyFields'])
                    && $type['implements'][$interface['name']]['copyFields'] === "true"
                ) {
                    $types[$name]['fields'] = isset($type['fields'])
                        ? array_merge($type['fields'], $interface['fields']) : $interface['fields'];
                }
            }
        }

        return array_merge($types, $interfaces);
    }

    /**
     * Normalize all output types inside of GraphQL configuration array.
     *
     * @param array $source
     * @return array
     */
    private function normalizeTypes(array $source): array
    {
        return $this->normalizeStructure(
            $source,
            'graphql_type',
            'OutputType',
            ['name']
        );
    }

    /**
     * Normalize all output interfaces inside of GraphQL configuration array.
     *
     * @param array $source
     * @return array
     */
    private function normalizeInterfaces(array $source): array
    {
        return $this->normalizeStructure(
            $source,
            'graphql_interface',
            'OutputInterface',
            ['name', 'typeResolver']
        );
    }

    /**
     * Output normalized array read from GraphQL configuration.
     *
     * @param array $source
     * @param string $configKey
     * @param string $configType
     * @param array $requiredAttributes
     * @return array
     */
    private function normalizeStructure(
        array $source,
        string $configKey,
        string $configType,
        array $requiredAttributes
    ) : array {
        $entries = [];
        foreach ($source['config'][0]['type'] as $entry) {
            if ($entry['type'] !== $configType) {
                continue;
            }
            $entries[$entry['name']] = array_intersect_key($entry, array_flip($requiredAttributes));
            $entries[$entry['name']]['type'] = $configKey;
            $entries[$entry['name']] = array_merge($entries[$entry['name']], $this->formatter->format($entry));
        }
        return $entries;
    }
}
