<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\GraphQl\Config\Converter\Normalizer;

use Magento\Framework\GraphQl\Config\Converter\NormalizerInterface;

/**
 * Normalize enum types to fit requisite structure for building a GraphQL schema.
 */
class Enum implements NormalizerInterface
{
    /**
     * {@inheritDoc}
     */
    public function normalize(array $source): array
    {
        $enums = [];
        foreach ($source['config'][0]['type'] as $entry) {
            if ($entry['type'] !== 'Enum') {
                continue;
            }
            if (isset($entry['description'])) {
                $enums[$entry['name']]['description'] = implode(PHP_EOL, $entry['description']);
            }
            $enums[$entry['name']]['name'] = $entry['name'];
            $enums[$entry['name']]['type'] = 'graphql_enum';
            foreach ($entry['item'] as $item) {
                $enums[$entry['name']]['items'][$item['_value']] = $item;
            }
        }
        return $enums;
    }
}
