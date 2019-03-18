<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Ui\Component\Listing\Column\Status;

use Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Ui\Component\Listing\Column\Status\Options;

/**
 * Class OptionsTest for Magento\Sales\Ui\Component\Listing\Column\Status\Options.
 */
class OptionsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Options
     */
    protected $model;

    /**
     * @var CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $collectionFactoryMock;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->collectionFactoryMock = $this->getMock(
            \Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory::class,
            ['create'],
            [],
            '',
            false
        );
        $this->model = $objectManager->getObject(
            \Magento\Sales\Ui\Component\Listing\Column\Status\Options::class,
            ['collectionFactory' => $this->collectionFactoryMock]
        );
    }

    /**
     * Unit test for toOptionArray method.
     *
     * @return void
     */
    public function testToOptionArray()
    {
        $collectionMock = $this->getMock(
            \Magento\Sales\Model\ResourceModel\Order\Status\Collection::class,
            [],
            [],
            '',
            false
        );

        $options = [
            [
                'value' => '1',
                'label' => 'Label',
            ],
        ];

        $expectedOptions = [
            [
                'value' => '1',
                'label' => 'Label',
                '__disableTmpl' => true,
            ],
        ];

        $this->collectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($collectionMock);
        $collectionMock->expects($this->once())
            ->method('toOptionArray')
            ->willReturn($options);

        $this->assertEquals($expectedOptions, $this->model->toOptionArray());
    }
}
