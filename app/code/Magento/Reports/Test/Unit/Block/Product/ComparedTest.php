<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Reports\Test\Unit\Block\Product;

use \Magento\Reports\Block\Product\Compared;
use \Magento\Reports\Model\Product\Index\Factory;

class ComparedTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \Magento\Reports\Block\Product\Compared;
     */
    private $sut;

    /**
     * @var Factory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $factoryMock;

    protected function setUp()
    {
        $contextMock = $this->getMockBuilder(\Magento\Catalog\Block\Product\Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $visibilityMock = $this->getMockBuilder(\Magento\Catalog\Model\Product\Visibility::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->factoryMock = $this->getMockBuilder(Factory::class)
            ->disableOriginalConstructor()
            ->setMethods(['get'])
            ->getMock();

        $this->sut = new Compared($contextMock, $visibilityMock, $this->factoryMock);
    }

    /**
     * Assert that getModel method throws LocalizedException
     *
     * @expectedException \Magento\Framework\Exception\LocalizedException
     */
    public function testGetModelException()
    {
        $this->factoryMock->expects($this->once())->method('get')->willThrowException(new \InvalidArgumentException);

        $this->sut->getModel();
    }

    /**
     * Assert that getModel method returns AbstractIndex
     */
    public function testGetModel()
    {
        $indexMock = $this->getMockBuilder(\Magento\Reports\Model\Product\Index\AbstractIndex::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->factoryMock->expects($this->once())->method('get')->willReturn($indexMock);

        $this->assertSame($indexMock, $this->sut->getModel());
    }
}
