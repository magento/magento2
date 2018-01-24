<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\GraphQl\Config\Converter\Normalizer;

use Magento\Framework\GraphQl\Config\Converter\NormalizerInterface;
use Magento\Framework\GraphQl\Config\Converter\Type\FormatterInterface;

/**
 * Normalize input object types for consumption by Schema processors.
 */
class Input implements NormalizerInterface
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
        $configKey = 'graphql_input';
        $configType = 'InputType';
        $requiredAttributes = ['name'];

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
