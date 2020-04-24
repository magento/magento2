<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Model\Indexer\Product\Eav\Plugin;

use Magento\Catalog\Model\Indexer\Product\Eav\Plugin\StoreView;
use Magento\Catalog\Model\Indexer\Product\Eav\Processor;
use Magento\Framework\Model\AbstractModel;
use Magento\Store\Model\ResourceModel\Store;
use PHPUnit\Framework\TestCase;

class StoreViewTest extends TestCase
{
    /**
     * @param array $data
     * @dataProvider beforeSaveDataProvider
     */
    public function testBeforeSave(array $data)
    {
        $eavProcessorMock = $this->getMockBuilder(Processor::class)
            ->disableOriginalConstructor()
            ->getMock();
        $matcher = $data['matcher'];
        $eavProcessorMock->expects($this->$matcher())
            ->method('markIndexerAsInvalid');

        $subjectMock = $this->getMockBuilder(Store::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectMock = $this->getMockBuilder(AbstractModel::class)
            ->disableOriginalConstructor()
            ->setMethods(['getId', 'dataHasChangedFor', 'getIsActive'])
            ->getMock();
        $objectMock->expects($this->any())
            ->method('getId')
            ->willReturn($data['object_id']);
        $objectMock->expects($this->any())
            ->method('dataHasChangedFor')
            ->with('group_id')
            ->willReturn($data['has_group_id_changed']);
        $objectMock->expects($this->any())
            ->method('getIsActive')
            ->willReturn($data['is_active']);

        $model = new StoreView($eavProcessorMock);
        $model->beforeSave($subjectMock, $objectMock);
    }

    /**
     * @return array
     */
    public function beforeSaveDataProvider()
    {
        return [
            [
                [
                    'matcher' => 'once',
                    'object_id' => 1,
                    'has_group_id_changed' => true,
                    'is_active' => true,
                ],
            ],
            [
                [
                    'matcher' => 'never',
                    'object_id' => 1,
                    'has_group_id_changed' => false,
                    'is_active' => true,
                ]
            ],
            [
                [
                    'matcher' => 'never',
                    'object_id' => 1,
                    'has_group_id_changed' => true,
                    'is_active' => false,
                ]
            ],
            [
                [
                    'matcher' => 'never',
                    'object_id' => 1,
                    'has_group_id_changed' => false,
                    'is_active' => false,
                ]
            ],
            [
                [
                    'matcher' => 'once',
                    'object_id' => 0,
                    'has_group_id_changed' => true,
                    'is_active' => true,
                ]
            ],
            [
                [
                    'matcher' => 'once',
                    'object_id' => 0,
                    'has_group_id_changed' => false,
                    'is_active' => true,
                ]
            ],
            [
                [
                    'matcher' => 'never',
                    'object_id' => 0,
                    'has_group_id_changed' => true,
                    'is_active' => false,
                ]
            ],
            [
                [
                    'matcher' => 'never',
                    'object_id' => 0,
                    'has_group_id_changed' => false,
                    'is_active' => false,
                ]
            ],
        ];
    }
}
