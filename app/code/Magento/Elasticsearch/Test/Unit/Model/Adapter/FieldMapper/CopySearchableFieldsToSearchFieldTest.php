<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch\Test\Unit\Model\Adapter\FieldMapper;

use Magento\Elasticsearch\Model\Adapter\FieldMapper\CopySearchableFieldsToSearchField;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

/**
 * Test mapping preprocessor CopySearchableFieldsToSearchField
 */
class CopySearchableFieldsToSearchFieldTest extends TestCase
{
    /**
     * Test "copy_to" parameter should be added to searchable fields.
     *
     * @dataProvider processDataProvider
     * @param array $mappingBefore
     * @param array $mappingAfter
     */
    public function testProcess(array $mappingBefore, array $mappingAfter)
    {
        $objectManager = new ObjectManager($this);
        $model = $objectManager->getObject(CopySearchableFieldsToSearchField::class);
        $this->assertEquals($mappingAfter, $model->process($mappingBefore));
    }

    /**
     * @return array
     */
    public function processDataProvider(): array
    {
        return [
            'index text field should be copied' => [
                [
                    'name' => [
                        'type' => 'text'
                    ]
                ],
                [
                    'name' => [
                        'type' => 'text',
                        'copy_to' => [
                            '_search'
                        ]
                    ]
                ]
            ],
            'non-index text field should not be copied' => [
                [
                    'name' => [
                        'type' => 'text',
                        'index' => false
                    ]
                ],
                [
                    'name' => [
                        'type' => 'text',
                        'index' => false
                    ]
                ]
            ],
            'index keyword field should be copied' => [
                [
                    'material' => [
                        'type' => 'keyword'
                    ]
                ],
                [
                    'material' => [
                        'type' => 'keyword',
                        'copy_to' => [
                            '_search'
                        ]
                    ]
                ]
            ],
            'non-index keyword field should not be copied' => [
                [
                    'country_of_manufacture' => [
                        'type' => 'keyword',
                        'index' => false
                    ]
                ],
                [
                    'country_of_manufacture' => [
                        'type' => 'keyword',
                        'index' => false
                    ]
                ]
            ],
            'index integer field should not be copied' => [
                [
                    'sale' => [
                        'type' => 'integer',
                    ]
                ],
                [
                    'sale' => [
                        'type' => 'integer',
                    ]
                ]
            ],
            'non-index integer field should not be copied' => [
                [
                    'position_category_1' => [
                        'type' => 'integer',
                        'index' => false
                    ]
                ],
                [
                    'position_category_1' => [
                        'type' => 'integer',
                        'index' => false
                    ]
                ]
            ],
        ];
    }
}
