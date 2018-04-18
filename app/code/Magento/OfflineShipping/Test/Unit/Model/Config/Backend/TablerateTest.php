<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\OfflineShipping\Test\Unit\Model\Config\Backend;

class TablerateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\OfflineShipping\Model\Config\Backend\Tablerate
     */
    protected $model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $tableateFactoryMock;

    protected function setUp()
    {
        $this->tableateFactoryMock =
            $this->getMockBuilder('Magento\OfflineShipping\Model\ResourceModel\Carrier\TablerateFactory')
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $helper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->model = $helper->getObject('\Magento\OfflineShipping\Model\Config\Backend\Tablerate', [
            'tablerateFactory' => $this->tableateFactoryMock
        ]);
    }

    public function testAfterSave()
    {
        $tablerateMock = $this->getMockBuilder('Magento\OfflineShipping\Model\ResourceModel\Carrier\Tablerate')
            ->disableOriginalConstructor()
            ->setMethods(['uploadAndImport'])
            ->getMock();

        $this->tableateFactoryMock->expects($this->once())
            ->method('create')
            ->willReturn($tablerateMock);

        $tablerateMock->expects($this->once())
            ->method('uploadAndImport')
            ->with($this->model);

        $this->model->afterSave();
    }
}
