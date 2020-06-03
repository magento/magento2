<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\ImportExport\Test\Unit\Model\Source\Export;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\ImportExport\Model\Export\ConfigInterface;
use Magento\ImportExport\Model\Source\Export\Entity;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class EntityTest extends TestCase
{
    /**
     * @var ConfigInterface|MockObject
     */
    private $exportConfigMock;

    /**
     * @var Entity
     */
    private $model;

    /**
     * Setup environment for test
     */
    protected function setUp(): void
    {
        $this->exportConfigMock = $this->getMockForAbstractClass(ConfigInterface::class);

        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            Entity::class,
            [
                'exportConfig' => $this->exportConfigMock
            ]
        );
    }

    /**
     * Test toOptionArray with data provider
     *
     * @param array $entities
     * @param array $expected
     * @dataProvider toOptionArrayDataProvider
     */
    public function testToOptionArray($entities, $expected)
    {
        $this->exportConfigMock->expects($this->any())->method('getEntities')->willReturn($entities);

        $this->assertEquals($expected, $this->model->toOptionArray());
    }

    /**
     * Data Provider for test toOptionArray
     *
     * @return array
     */
    public function toOptionArrayDataProvider()
    {
        return [
            'Empty Entity' => [
                [],
                [
                    [
                        'label' => (string)__('-- Please Select --'),
                        'value' => ''
                    ]
                ]
            ],
            'Has entities' => [
                [
                    'entity1' => [
                        'label' => 'Entity 1'
                    ]
                ],
                [
                    [
                        'label' => (string)__('-- Please Select --'),
                        'value' => ''
                    ],
                    [
                        'label' => (string)__('Entity 1'),
                        'value' => 'entity1'
                    ]
                ]
            ]
        ];
    }
}
