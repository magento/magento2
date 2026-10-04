<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
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
        $this->_compositeFactory = $this->createPartialMock(
            CompositeFactory::class,
            ['create']
        );
    }

    public function testCreate()
    {
        $specification = $this->createMock(SpecificationInterface::class);
        $specificationMapping = [self::SPECIFICATION_KEY => $specification];

        $expectedComposite = $this->createMock(Composite::class);
        $modelFactory = new SpecificationFactory($this->_compositeFactory, $specificationMapping);
        $this->_compositeFactory->expects($this->once())->method('create')->with(
            ['list' => $specificationMapping]
        )->willReturn($expectedComposite);

        $this->assertEquals($expectedComposite, $modelFactory->create([self::SPECIFICATION_KEY]));
    }
}
