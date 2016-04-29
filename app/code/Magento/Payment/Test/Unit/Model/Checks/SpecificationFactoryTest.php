<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Payment\Test\Unit\Model\Checks;

use \Magento\Payment\Model\Checks\SpecificationFactory;

class SpecificationFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Specification key
     */
    const SPECIFICATION_KEY = 'specification';

    /**
     * @var \Magento\Payment\Model\Checks\CompositeFactory | \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_compositeFactory;

    protected function setUp()
    {
        $this->_compositeFactory = $this->getMockBuilder(
            \Magento\Payment\Model\Checks\CompositeFactory::class
        )->disableOriginalConstructor()->setMethods(['create'])->getMock();
    }

    public function testCreate()
    {
        $specification = $this->getMockBuilder(
            \Magento\Payment\Model\Checks\SpecificationInterface::class
        )->disableOriginalConstructor()->setMethods([])->getMock();
        $specificationMapping = [self::SPECIFICATION_KEY => $specification];

        $expectedComposite = $this->getMockBuilder(
            \Magento\Payment\Model\Checks\Composite::class
        )->disableOriginalConstructor()->setMethods([])->getMock();
        $modelFactory = new SpecificationFactory($this->_compositeFactory, $specificationMapping);
        $this->_compositeFactory->expects($this->once())->method('create')->with(
            ['list' => $specificationMapping]
        )->will($this->returnValue($expectedComposite));

        $this->assertEquals($expectedComposite, $modelFactory->create([self::SPECIFICATION_KEY]));
    }
}
