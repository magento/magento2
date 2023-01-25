<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\ObjectManager\Factory;

use Magento\Framework\ObjectManager\TestAsset\Basic;
use Magento\Framework\ObjectManager\TestAsset\BasicInjection;
use Magento\Framework\ObjectManager\TestAsset\ComplexDependencies;
use Magento\Framework\ObjectManager\TestAsset\ConstructorNineArguments;
use Magento\Framework\ObjectManager\TestAsset\DependsOnAlias;
use Magento\Framework\ObjectManager\TestAsset\DependsOnInterface;
use Magento\Framework\ObjectManager\TestAsset\HasOptionalParameters;
use Magento\Framework\ObjectManager\TestAsset\TestAssetInterface;

abstract class AbstractFactoryRuntimeDefinitionsTestCases extends \PHPUnit\Framework\TestCase
{
    const ALIAS_OVERRIDDEN_STRING = 'overridden';
    const ALIAS_OVERRIDDEN_INT = 99;

    /** @var ComplexDependencies */
    protected $complexDependenciesObject;

    /** @var AbstractFactory */
    protected $factory;

    /**
     * Child test cases should create this object using the type of factory they are testing
     *
     * @return AbstractFactory
     */
    abstract protected function createFactoryToTest();

    protected function setUp(): void
    {
        $this->factory = $this->createFactoryToTest();

        /**
         * Test creates one object which depends on all the other kind of objects whose creation we need to test. This
         * means the test can not only test creating each of the varied constructor scenarios (e.g., a class with
         * optional constructor parameters) but also test creating an object which *depends* on each of the varied
         * scenarios.
         */
        $this->complexDependenciesObject = $this->factory->create(ComplexDependencies::class);
    }

    public function testCreateComplexDependencies()
    {
        $this->assertInstanceOf(ComplexDependencies::class, $this->complexDependenciesObject);
    }

    public function testCreateBasic()
    {
        $this->assertInstanceOf(Basic::class, $this->complexDependenciesObject->getBasic());
    }

    public function testCreateBasicInjection()
    {
        $this->assertInstanceOf(BasicInjection::class, $this->complexDependenciesObject->getBasicInjection());
        $this->assertInstanceOf(
            Basic::class,
            $this->complexDependenciesObject->getBasicInjection()->getBasicDependency()
        );
    }

    public function testCreateInterface()
    {
        $this->assertInstanceOf(TestAssetInterface::class, $this->complexDependenciesObject->getTestAssetInterface());
    }

    public function testCreateConstructorNestedInjection()
    {
        $this->assertInstanceOf(
            ConstructorNineArguments::class,
            $this->complexDependenciesObject->getConstructorNineArguments()
        );
        $this->assertInstanceOf(
            Basic::class,
            $this->complexDependenciesObject->getConstructorNineArguments()->getBasicDependency()
        );
    }

    public function testCreateObjectDependsOnInterface()
    {
        $this->assertInstanceOf(DependsOnInterface::class, $this->complexDependenciesObject->getDependsOnInterface());
        $this->assertInstanceOf(
            TestAssetInterface::class,
            $this->complexDependenciesObject->getDependsOnInterface()->getInterfaceDependency()
        );
    }

    public function testCreateObjectHasOptionalParameters()
    {
        $this->assertInstanceOf(
            HasOptionalParameters::class,
            $this->complexDependenciesObject->getHasOptionalParameters()
        );
        $this->assertEquals(
            HasOptionalParameters::CONSTRUCTOR_INT_PARAM_DEFAULT,
            $this->complexDependenciesObject->getHasOptionalParameters()->getOptionalIntegerParameter()
        );
        $this->assertEquals(
            HasOptionalParameters::CONSTRUCTOR_STRING_PARAM_DEFAULT,
            $this->complexDependenciesObject->getHasOptionalParameters()->getOptionalStringParameter()
        );
        $this->assertInstanceOf(
            TestAssetInterface::class,
            $this->complexDependenciesObject->getHasOptionalParameters()->getRequiredInterfaceParam()
        );
        $this->assertInstanceOf(
            Basic::class,
            $this->complexDependenciesObject->getHasOptionalParameters()->getRequiredObjectParameter()
        );
    }

    public function testCreateObjectDependsOnAlias()
    {
        $this->assertInstanceOf(DependsOnAlias::class, $this->complexDependenciesObject->getDependsOnAlias());
        $this->assertEquals(
            self::ALIAS_OVERRIDDEN_STRING,
            $this->complexDependenciesObject->getDependsOnAlias()->getOverriddenString()
        );
        $this->assertEquals(
            self::ALIAS_OVERRIDDEN_INT,
            $this->complexDependenciesObject->getDependsOnAlias()->getOverRiddenInteger()
        );
    }
}
