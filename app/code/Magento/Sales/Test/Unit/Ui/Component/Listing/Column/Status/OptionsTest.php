<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Sales\Test\Unit\Ui\Component\Listing\Column\Status;

use Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Ui\Component\Listing\Column\Status\Options;

/**
 * Class OptionsTest
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

    protected function setUp()
    {
        $objectManager = new ObjectManager($this);
        $this->collectionFactoryMock = $this->getMock(
            'Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory',
            ['create'],
            [],
            '',
            false
        );
        $this->model = $objectManager->getObject(
            'Magento\Sales\Ui\Component\Listing\Column\Status\Options',
            ['collectionFactory' => $this->collectionFactoryMock]
        );
    }

    public function testToOptionArray()
    {
        $collectionMock =
            $this->getMock('Magento\Sales\Model\ResourceModel\Order\Status\Collection', [], [], '', false);
        $options = ['options'];

        $this->collectionFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($collectionMock);
        $collectionMock->expects($this->once())
            ->method('toOptionArray')
            ->willReturn($options);
        $this->assertEquals($options, $this->model->toOptionArray());
        $this->assertEquals($options, $this->model->toOptionArray());
    }
}
