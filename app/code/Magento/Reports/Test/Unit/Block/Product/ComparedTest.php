<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Reports\Test\Unit\Block\Product;

use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Model\Product\Visibility;
use Magento\Reports\Block\Product\Compared;
use Magento\Reports\Model\Product\Index\AbstractIndex;
use Magento\Reports\Model\Product\Index\Factory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ComparedTest extends TestCase
{

    /**
     * @var Compared ;
     */
    private $sut;

    /**
     * @var Factory|MockObject
     */
    private $factoryMock;

    protected function setUp(): void
    {
        $contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $visibilityMock = $this->getMockBuilder(Visibility::class)
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
     */
    public function testGetModelException()
    {
        $this->expectException('Magento\Framework\Exception\LocalizedException');
        $this->factoryMock->expects($this->once())->method('get')->willThrowException(new \InvalidArgumentException());

        $this->sut->getModel();
    }

    /**
     * Assert that getModel method returns AbstractIndex
     */
    public function testGetModel()
    {
        $indexMock = $this->getMockBuilder(AbstractIndex::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->factoryMock->expects($this->once())->method('get')->willReturn($indexMock);

        $this->assertSame($indexMock, $this->sut->getModel());
    }
}
