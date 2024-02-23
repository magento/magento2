<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Payment\Test\Unit\Model\Checks;

use Magento\Payment\Model\Checks\Composite;
use Magento\Payment\Model\Checks\CompositeFactory;
use Magento\Payment\Model\Checks\SpecificationFactory;
use Magento\Payment\Model\Checks\SpecificationInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SpecificationFactoryTest extends TestCase
{
    private const SPECIFICATION_KEY = 'specification';

    /**
     * @var CompositeFactory|MockObject
     */
    protected $_compositeFactory;

    protected function setUp(): void
    {
        $this->_compositeFactory = $this->getMockBuilder(
            CompositeFactory::class
        )->disableOriginalConstructor()
            ->onlyMethods(['create'])->getMock();
    }

    public function testCreate()
    {
        $specification = $this->getMockBuilder(
            SpecificationInterface::class
        )->disableOriginalConstructor()->getMock();
        $specificationMapping = [self::SPECIFICATION_KEY => $specification];

        $expectedComposite = $this->getMockBuilder(
            Composite::class
        )->disableOriginalConstructor()->getMock();
        $modelFactory = new SpecificationFactory($this->_compositeFactory, $specificationMapping);
        $this->_compositeFactory->expects($this->once())->method('create')->with(
            ['list' => $specificationMapping]
        )->willReturn($expectedComposite);

        $this->assertEquals($expectedComposite, $modelFactory->create([self::SPECIFICATION_KEY]));
    }
}
