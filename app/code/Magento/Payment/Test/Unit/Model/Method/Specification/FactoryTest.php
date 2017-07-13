<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Test\Unit\Model\Method\Specification;

/**
 * Factory Test
 */
class FactoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $objectManagerMock;

    /**
     * @var \Magento\Payment\Model\Method\Specification\Factory
     */
    protected $factory;

    protected function setUp()
    {
        $this->objectManagerMock = $this->createMock(\Magento\Framework\ObjectManagerInterface::class);

        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->factory = $objectManagerHelper->getObject(
            \Magento\Payment\Model\Method\Specification\Factory::class,
            ['objectManager' => $this->objectManagerMock]
        );
    }

    public function testCreateMethod()
    {
        $className = \Magento\Payment\Model\Method\SpecificationInterface::class;
        $methodMock = $this->createMock($className);
        $this->objectManagerMock->expects(
            $this->once()
        )->method(
            'get'
        )->with(
            $className
        )->will(
            $this->returnValue($methodMock)
        );

        $this->assertEquals($methodMock, $this->factory->create($className));
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Specification must implement SpecificationInterface
     */
    public function testWrongTypeException()
    {
        $className = 'WrongClass';
        $methodMock = $this->createMock($className);
        $this->objectManagerMock->expects(
            $this->once()
        )->method(
            'get'
        )->with(
            $className
        )->will(
            $this->returnValue($methodMock)
        );

        $this->factory->create($className);
    }
}
