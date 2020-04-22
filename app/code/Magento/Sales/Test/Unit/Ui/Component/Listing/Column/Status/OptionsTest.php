<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Ui\Component\Listing\Column\Status;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory;
use Magento\Sales\Ui\Component\Listing\Column\Status\Options;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Class Options test for Listing Column Status
 */
class OptionsTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var Options
     */
    protected $model;

    /**
     * @var CollectionFactory|MockObject
     */
    protected $collectionFactoryMock;

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->collectionFactoryMock = $this->createPartialMock(
            \Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory::class,
            ['create']
        );
        $this->model = $objectManager->getObject(
            \Magento\Sales\Ui\Component\Listing\Column\Status\Options::class,
            ['collectionFactory' => $this->collectionFactoryMock]
        );
    }

    public function testToOptionArray()
    {
        $collectionMock = $this->createMock(
            \Magento\Sales\Model\ResourceModel\Order\Status\Collection::class
        );

        $options = [
            [
                'value' => '1',
                'label' => 'Label'
            ]
        ];

        $expectedOptions = [
            [
                'value' => '1',
                'label' => 'Label',
            ]
        ];

        $this->collectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($collectionMock);

        $collectionMock->expects($this->once())
            ->method('toOptionArray')
            ->willReturn($options);

        $this->assertEquals($expectedOptions, $this->model->toOptionArray());
        $this->assertEquals($expectedOptions, $this->model->toOptionArray());
    }
}
