<?php
/**
 * Copyright © 2013-2018 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\ObjectManager\TestAsset;

class ComplexDependencies
{
    /**
     * @var Basic
     */
    private $basic;

    /**
     * @var BasicInjection
     */
    private $basicInjection;

    /**
     * @var DependsOnInterface
     */
    private $dependsOnInterface;

    /**
     * @var HasOptionalParameters
     */
    private $hasOptionalParameters;

    /**
     * @var TestAssetInterface
     */
    private $testAssetInterface;

    /**
     * @var ConstructorNineArguments
     */
    private $constructorNineArguments;

    /**
     * @var DependsOnAlias
     */
    private $dependsOnAlias;

    /**
     * @param Basic $basic
     * @param BasicInjection $basicInjection
     * @param DependsOnInterface $dependsOnInterface
     * @param HasOptionalParameters $hasOptionalParameters
     * @param TestAssetInterface $testAssetInterface
     * @param ConstructorNineArguments $constructorNineArguments
     * @param DependsOnAlias $dependsOnAlias
     */
    public function __construct(
        \Magento\Framework\ObjectManager\TestAsset\Basic $basic,
        \Magento\Framework\ObjectManager\TestAsset\BasicInjection $basicInjection,
        \Magento\Framework\ObjectManager\TestAsset\DependsOnInterface $dependsOnInterface,
        \Magento\Framework\ObjectManager\TestAsset\HasOptionalParameters $hasOptionalParameters,
        \Magento\Framework\ObjectManager\TestAsset\TestAssetInterface $testAssetInterface,
        \Magento\Framework\ObjectManager\TestAsset\ConstructorNineArguments $constructorNineArguments,
        DependsOnAlias $dependsOnAlias
    ) {
        $this->basic = $basic;
        $this->basicInjection = $basicInjection;
        $this->dependsOnInterface = $dependsOnInterface;
        $this->hasOptionalParameters = $hasOptionalParameters;
        $this->testAssetInterface = $testAssetInterface;
        $this->constructorNineArguments = $constructorNineArguments;
        $this->dependsOnAlias = $dependsOnAlias;
    }

    /**
     * @return DependsOnAlias
     */
    public function getDependsOnAlias()
    {
        return $this->dependsOnAlias;
    }

    /**
     * @return Basic
     */
    public function getBasic()
    {
        return $this->basic;
    }

    /**
     * @return BasicInjection
     */
    public function getBasicInjection()
    {
        return $this->basicInjection;
    }

    /**
     * @return DependsOnInterface
     */
    public function getDependsOnInterface()
    {
        return $this->dependsOnInterface;
    }

    /**
     * @return HasOptionalParameters
     */
    public function getHasOptionalParameters()
    {
        return $this->hasOptionalParameters;
    }

    /**
     * @return TestAssetInterface
     */
    public function getTestAssetInterface()
    {
        return $this->testAssetInterface;
    }

    /**
     * @return ConstructorNineArguments
     */
    public function getConstructorNineArguments()
    {
        return $this->constructorNineArguments;
    }
}
