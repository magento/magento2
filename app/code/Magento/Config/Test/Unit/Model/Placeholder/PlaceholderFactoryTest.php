<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Test\Unit\Model\Placeholder;

use Magento\Config\Model\Placeholder\Environment;
use Magento\Config\Model\Placeholder\PlaceholderFactory;
use Magento\Framework\ObjectManagerInterface;

class PlaceholderFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PlaceholderFactory
     */
    private $model;

    /**
     * @var ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $objectManagerMock;

    /**
     * @var Environment|\PHPUnit_Framework_MockObject_MockObject
     */
    private $environmentMock;

    protected function setUp()
    {
        $this->objectManagerMock = $this->getMockBuilder(ObjectManagerInterface::class)
            ->getMockForAbstractClass();
        $this->environmentMock = $this->getMockBuilder(Environment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new PlaceholderFactory(
            $this->objectManagerMock,
            [
                PlaceholderFactory::TYPE_ENVIRONMENT => Environment::class,
                'wrongClass' => \stdClass::class,
            ]
        );
    }

    public function testCreate()
    {
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->with(Environment::class)
            ->willReturn($this->environmentMock);

        $this->assertInstanceOf(
            Environment::class,
            $this->model->create(PlaceholderFactory::TYPE_ENVIRONMENT)
        );
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage There is no defined type dummyClass
     */
    public function testCreateNonExisted()
    {
        $this->model->create('dummyClass');
    }

    /**
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @expectedExceptionMessage Object is not instance of Magento\Config\Model\Placeholder\PlaceholderInterface
     */
    public function testCreateWrongImplementation()
    {
        $this->model->create('wrongClass');
    }
}
