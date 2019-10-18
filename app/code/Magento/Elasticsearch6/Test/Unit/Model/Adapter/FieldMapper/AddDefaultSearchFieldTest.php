<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Elasticsearch6\Test\Unit\Model\Adapter\FieldMapper;

use Magento\Elasticsearch6\Model\Adapter\FieldMapper\AddDefaultSearchField;
use Magento\Framework\TestFramework\Unit\BaseTestCase;

/**
 * Test mapping preprocessor AddDefaultSearchField
 */
class AddDefaultSearchFieldTest extends BaseTestCase
{
    /**
     * @var AddDefaultSearchField
     */
    private $model;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();
        $this->model = $this->objectManager->getObject(AddDefaultSearchField::class);
    }

    /**
     * Test default search field "_search" should be prepended and overwrite if exist.
     *
     * @dataProvider processDataProvider
     * @param array $mappingBefore
     * @param array $mappingAfter
     */
    public function testProcess(array $mappingBefore, array $mappingAfter)
    {
        $this->assertEquals($mappingAfter, $this->model->process($mappingBefore));
    }

    /**
     * @return array
     */
    public function processDataProvider(): array
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
