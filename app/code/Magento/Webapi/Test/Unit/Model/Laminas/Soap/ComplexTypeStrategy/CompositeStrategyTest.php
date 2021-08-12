<?php

/**
 * @see       https://github.com/laminas/laminas-soap for the canonical source repository
 * @copyright https://github.com/laminas/laminas-soap/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-soap/blob/master/LICENSE.md New BSD License
 */

namespace Magento\Webapi\Test\Unit\Model\Laminas\Soap\ComplexTypeStrategy;

use Magento\Webapi\Model\Laminas\Soap\Exception\InvalidArgumentException;
use Magento\Webapi\Model\Laminas\Soap\ComplexTypeStrategy\AnyType;
use Magento\Webapi\Model\Laminas\Soap\ComplexTypeStrategy\ArrayOfTypeComplex;
use Magento\Webapi\Model\Laminas\Soap\ComplexTypeStrategy\ArrayOfTypeSequence;
use Magento\Webapi\Model\Laminas\Soap\ComplexTypeStrategy\Composite;
use Magento\Webapi\Model\Laminas\Soap\ComplexTypeStrategy\DefaultComplexType;
use Magento\Webapi\Test\Unit\Model\Laminas\Soap\WsdlTestHelper;
use Magento\Webapi\Test\Unit\Model\Laminas\Soap\TestAsset;

/**
 * @group      Laminas_Soap
 * @group      Laminas_Soap_Wsdl
 */
class CompositeStrategyTest extends WsdlTestHelper
{
    public function setUp(): void
    {
        // override parent setup because it is needed only in one method
    }

    public function testCompositeApiAddingStragiesToTypes()
    {
        $strategy = new Composite([], new ArrayOfTypeSequence);
        $strategy->connectTypeToStrategy('Book', new ArrayOfTypeComplex);

        $bookStrategy = $strategy->getStrategyOfType('Book');
        $cookieStrategy = $strategy->getStrategyOfType('Cookie');

        $this->assertInstanceOf(ArrayOfTypeComplex::class, $bookStrategy);
        $this->assertInstanceOf(
            ArrayOfTypeSequence::class,
            $cookieStrategy
        );
    }

    public function testConstructorTypeMapSyntax()
    {
        $typeMap = ['Book' => ArrayOfTypeComplex::class];

        $strategy = new Composite(
            $typeMap,
            new ArrayOfTypeSequence ()
        );

        $bookStrategy = $strategy->getStrategyOfType('Book');
        $cookieStrategy = $strategy->getStrategyOfType('Cookie');

        $this->assertInstanceOf(ArrayOfTypeComplex::class, $bookStrategy);
        $this->assertInstanceOf(
            ArrayOfTypeSequence::class,
            $cookieStrategy
        );
    }

    public function testCompositeThrowsExceptionOnInvalidType()
    {
        $strategy = new Composite();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid type given to Composite Type Map');
        $strategy->connectTypeToStrategy([], 'strategy');
    }

    public function testCompositeThrowsExceptionOnInvalidStrategy()
    {
        $strategy = new Composite([], 'invalid');
        $strategy->connectTypeToStrategy('Book', 'strategy');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Strategy for Complex Type "Book" is not a valid strategy');
        $strategy->getStrategyOfType('Book');
    }

    public function testCompositeThrowsExceptionOnInvalidStrategyPart2()
    {
        $strategy = new Composite([], 'invalid');
        $strategy->connectTypeToStrategy('Book', 'strategy');

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Default Strategy for Complex Types is not a valid strategy object');
        $strategy->getStrategyOfType('Anything');
    }

    public function testCompositeDelegatesAddingComplexTypesToSubStrategies()
    {
        $this->strategy = new Composite([], new AnyType);
        $this->strategy->connectTypeToStrategy(
            TestAsset\CompositeStrategyTest\Book::class,
            new ArrayOfTypeComplex
        );
        $this->strategy->connectTypeToStrategy(
            TestAsset\CompositeStrategyTest\Cookie::class,
            new DefaultComplexType
        );

        parent::setUp();

        $this->assertEquals(
            'tns:Book',
            $this->strategy->addComplexType(TestAsset\CompositeStrategyTest\Book::class)
        );
        $this->assertEquals(
            'tns:Cookie',
            $this->strategy->addComplexType(TestAsset\CompositeStrategyTest\Cookie::class)
        );
        $this->assertEquals(
            'xsd:anyType',
            $this->strategy->addComplexType(TestAsset\CompositeStrategyTest\Anything::class)
        );

        $this->documentNodesTest();
    }

    public function testCompositeRequiresContextForAddingComplexTypesOtherwiseThrowsException()
    {
        $strategy = new Composite();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot add complex type "Test"');
        $strategy->addComplexType('Test');
    }

    public function testGetDefaultStrategy()
    {
        $strategyClass = AnyType::class;

        $strategy = new Composite([], $strategyClass);

        $this->assertEquals($strategyClass, get_class($strategy->getDefaultStrategy()));
    }
}
