<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Test\Unit\Model\Method\Specification;

/**
 * Composite Test
 */
class CompositeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Payment\Model\Method\Specification\Factory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $factoryMock;

    protected function setUp()
    {
        $this->factoryMock = $this->getMock(
            'Magento\Payment\Model\Method\Specification\Factory',
            [],
            [],
            '',
            false
        );
    }

    /**
     * @param array $specifications
     * @return \Magento\Payment\Model\Method\Specification\Composite
     */
    protected function createComposite($specifications = [])
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        return $objectManager->getObject(
            'Magento\Payment\Model\Method\Specification\Composite',
            ['factory' => $this->factoryMock, 'specifications' => $specifications]
        );
    }

    /**
     * @param bool $firstSpecificationResult
     * @param bool $secondSpecificationResult
     * @param bool $compositeResult
     * @dataProvider compositeDataProvider
     */
    public function testComposite($firstSpecificationResult, $secondSpecificationResult, $compositeResult)
    {
        $method = 'method-name';

        $specificationFirst = $this->getMock('Magento\Payment\Model\Method\SpecificationInterface');
        $specificationFirst->expects(
            $this->once()
        )->method(
            'isSatisfiedBy'
        )->with(
            $method
        )->will(
            $this->returnValue($firstSpecificationResult)
        );

        $specificationSecond = $this->getMock('Magento\Payment\Model\Method\SpecificationInterface');
        $specificationSecond->expects(
            $this->any()
        )->method(
            'isSatisfiedBy'
        )->with(
            $method
        )->will(
            $this->returnValue($secondSpecificationResult)
        );

        $this->factoryMock->expects(
            $this->at(0)
        )->method(
            'create'
        )->with(
            'SpecificationFirst'
        )->will(
            $this->returnValue($specificationFirst)
        );
        $this->factoryMock->expects(
            $this->at(1)
        )->method(
            'create'
        )->with(
            'SpecificationSecond'
        )->will(
            $this->returnValue($specificationSecond)
        );

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
    public function compositeDataProvider()
    {
        return [
            [true, true, true],
            [true, false, false],
            [false, true, false],
            [false, false, false]
        ];
    }
}
