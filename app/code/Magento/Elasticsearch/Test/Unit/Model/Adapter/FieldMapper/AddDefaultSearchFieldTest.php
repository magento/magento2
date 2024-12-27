<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Test\Unit\Model\Adapter\FieldMapper;

use Magento\Elasticsearch\Model\Adapter\FieldMapper\AddDefaultSearchField;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Test mapping preprocessor AddDefaultSearchField
 */
class AddDefaultSearchFieldTest extends TestCase
{
    /**
     * Test default search field "_search" should be prepended and overwrite if exist.
     *
     * @dataProvider processDataProvider
     * @param array $mappingBefore
     * @param array $mappingAfter
     */
    public function testProcess(array $mappingBefore, array $mappingAfter)
    {
        $objectManager = new ObjectManager($this);
        $model = $objectManager->getObject(AddDefaultSearchField::class);
        $this->assertEquals($mappingAfter, $model->process($mappingBefore));
    }

    /**
     * @return array
     */
    public static function processDataProvider(): array
    {
        return [
            '_search field should be prepended if not exist' => [
                [
                    'name' => [
                        'type' => 'text'
                    ]
                ],
                [
                    '_search' => [
                        'type' => 'text'
                    ],
                    'name' => [
                        'type' => 'text'
                    ]
                ]
            ],
            '_search field should be prepended and overwrite if exist' => [
                [
                    'name' => [
                        'type' => 'text',
                    ],
                    '_search' => [
                        'type' => 'keyword'
                    ],
                ],
                [
                    '_search' => [
                        'type' => 'text'
                    ],
                    'name' => [
                        'type' => 'text',
                    ]
                ]
            ]
        ];
    }
}
