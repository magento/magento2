<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Payment\Test\Unit\Model\Method\Specification;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Payment\Model\Method\Specification\Composite;
use Magento\Payment\Model\Method\Specification\Factory;
use Magento\Payment\Model\Method\SpecificationInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CompositeTest extends TestCase
{
    /**
     * @var Factory|MockObject
     */
    protected $factoryMock;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->factoryMock = $this->createMock(Factory::class);
    }

    /**
     * @param array $specifications
     *
     * @return object
     */
    protected function createComposite(array $specifications = [])
    {
        $objectManager = new ObjectManager($this);

        return $objectManager->getObject(
            Composite::class,
            ['factory' => $this->factoryMock, 'specifications' => $specifications]
        );
    }

    /**
     * @param bool $firstSpecificationResult
     * @param bool $secondSpecificationResult
     * @param bool $compositeResult
     *
     * @return void
     * @dataProvider compositeDataProvider
     */
    public function testComposite(
        bool $firstSpecificationResult,
        bool $secondSpecificationResult,
        bool $compositeResult
    ): void {
        $method = 'method-name';

        $specificationFirst = $this->getMockForAbstractClass(SpecificationInterface::class);
        $specificationFirst->expects(
            $this->once()
        )->method(
            'isSatisfiedBy'
        )->with(
            $method
        )->willReturn(
            $firstSpecificationResult
        );

        $specificationSecond = $this->getMockForAbstractClass(SpecificationInterface::class);
        $specificationSecond->expects(
            $this->any()
        )->method(
            'isSatisfiedBy'
        )->with(
            $method
        )->willReturn(
            $secondSpecificationResult
        );

        $this->factoryMock
            ->method('create')
            ->willReturnCallback(fn($param) => match ([$param]) {
                ['SpecificationFirst'] =>  $specificationFirst,
                ['SpecificationSecond'] => $specificationSecond
            });

        $composite = $this->createComposite(['SpecificationFirst', 'SpecificationSecond']);

        $this->assertEquals(
            $compositeResult,
            $composite->isSatisfiedBy($method),
            'Composite specification is not satisfied by payment method'
        );
    }

    /**
     * @return array
     */
    public static function compositeDataProvider(): array
    {
        return [
            [true, true, true],
            [true, false, false],
            [false, true, false],
            [false, false, false]
        ];
    }
}
