<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Test\Unit\Model;

use Magento\SalesRule\Api\Data\CouponGenerationSpecInterface;
use Magento\SalesRule\Api\Data\CouponGenerationSpecInterfaceFactory;
use Magento\SalesRule\Model\CouponGenerator;
use Magento\SalesRule\Model\Service\CouponManagementService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Magento\SalesRule\Model\CouponGenerator
 */
class CouponGeneratorTest extends TestCase
{
    /**
     * Testable Object
     *
     * @var CouponGenerator
     */
    private $couponGenerator;

    /**
     * @var CouponManagementService|MockObject
     */
    private $couponManagementServiceMock;

    /**
     * @var CouponGenerationSpecInterfaceFactory|MockObject
     */
    private $generationSpecFactoryMock;

    /**
     * @var CouponGenerationSpecInterface|MockObject
     */
    private $generationSpecMock;

    /**
     * Set Up
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->generationSpecFactoryMock = $this->getMockBuilder(CouponGenerationSpecInterfaceFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])->getMock();
        $this->couponManagementServiceMock = $this->createMock(CouponManagementService::class);
        $this->generationSpecMock = $this->getMockForAbstractClass(CouponGenerationSpecInterface::class);
        $this->couponGenerator = new CouponGenerator(
            $this->couponManagementServiceMock,
            $this->generationSpecFactoryMock
        );
    }

    /**
     * Test beforeSave method
     *
     * @return void
     */
    public function testBeforeSave()
    {
        $expected = ['test'];
        $this->generationSpecFactoryMock->expects($this->once())->method('create')
            ->willReturn($this->generationSpecMock);
        $this->couponManagementServiceMock->expects($this->once())->method('generate')
            ->with($this->generationSpecMock)->willReturn($expected);
        $actual = $this->couponGenerator->generateCodes([]);
        self::assertEquals($expected, $actual);
    }
}
