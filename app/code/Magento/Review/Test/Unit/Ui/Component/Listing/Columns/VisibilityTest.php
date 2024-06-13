<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Review\Test\Unit\Ui\Component\Listing\Columns;

use Magento\Catalog\Test\Unit\Ui\Component\Listing\Columns\AbstractColumnTestCase;
use Magento\Review\Ui\Component\Listing\Columns\Visibility;
use Magento\Store\Model\System\Store;
use PHPUnit\Framework\MockObject\MockObject;

class VisibilityTest extends AbstractColumnTestCase
{
    /**
     * @var Store|MockObject
     */
    protected $storeMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->storeMock = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return Visibility
     */
    protected function getModel()
    {
        return $this->objectManager->getObject(Visibility::class, [
            'context' => $this->contextMock,
            'uiComponentFactory' => $this->uiComponentFactoryMock,
            'components' => [],
            'data' => [],
            'store' => $this->storeMock,
        ]);
    }

    public function testPrepareDataSource()
    {
        $dataSource = [
            'data' => [
                'items' => [
                    [
                        'stores' => [1]
                    ]
                ],
            ],
        ];
        $expectedVisibility =
            "Test Website<br/>&nbsp;&nbsp;&nbsp;Test group<br/>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Test store<br/>";
        $expectedDataSource = [
            'data' => [
                'items' => [
                    [
                        'stores' => [1],
                        'visibility' => $expectedVisibility,

                    ]
                ],
            ],
        ];

        $this->storeMock->expects($this->once())
            ->method('getStoresStructure')
            ->willReturn([
                [
                    'label' => 'Test Website',
                    'children' => [
                        [
                            'label' => 'Test group',
                            'children' => [
                                [
                                    'label' => 'Test store',
                                ]
                            ],
                        ]
                    ],
                ],
            ]);

        $this->assertEquals($expectedDataSource, $this->getModel()->prepareDataSource($dataSource));
    }
}
