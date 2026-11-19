<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\OpenSearch\Test\Unit\Model\Adapter\DynamicTemplates;

use Magento\OpenSearch\Model\Adapter\DynamicTemplatesProvider;
use PHPUnit\Framework\TestCase;

/**
 * Checks the dynamic templates in the order the module's di.xml configures them.
 */
class OwnValueSortMapperTest extends TestCase
{
    public function testOwnValueSortFieldsAreMappedAsKeywordBeforeTheStringTemplate(): void
    {
        $templates = $this->getConfiguredTemplates();
        $names = array_map(fn (array $template) => (string)array_key_first($template), $templates);
        $sortPosition = array_search('own_value_sort_mapping', $names, true);
        $stringPosition = array_search('string_mapping', $names, true);

        self::assertIsInt($sortPosition, 'own_value_sort_mapping template is missing');
        self::assertIsInt($stringPosition, 'string_mapping template is missing');
        self::assertLessThan($stringPosition, $sortPosition);
        self::assertSame(
            [
                'match' => '_sort_*',
                'match_mapping_type' => 'string',
                'mapping' => [
                    'type' => 'keyword',
                    'index' => false,
                    'normalizer' => 'folding',
                ],
            ],
            $templates[$sortPosition]['own_value_sort_mapping']
        );
    }

    /**
     * Build the templates from the mappers listed in etc/di.xml, in their configured order.
     *
     * @return array
     */
    private function getConfiguredTemplates(): array
    {
        $config = simplexml_load_file(dirname(__DIR__, 5) . '/etc/di.xml');
        $items = $config->xpath(
            '//type[@name="' . DynamicTemplatesProvider::class . '"]/arguments/argument[@name="mappers"]/item'
        );
        $mappers = [];
        foreach ($items as $item) {
            $class = trim((string)$item);
            $mappers[(string)$item['name']] = new $class();
        }

        return (new DynamicTemplatesProvider($mappers))->getTemplates();
    }
}
