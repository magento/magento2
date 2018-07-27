<?php
/**
 * Copyright © 2013-2018 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\ObjectManager\TestAsset;

class HasOptionalParameters
{
    const CONSTRUCTOR_STRING_PARAM_DEFAULT = 'default';
    const CONSTRUCTOR_INT_PARAM_DEFAULT = 0;

    /**
     * @var Basic
     */
    private $optionalObjectParameter;

    /**
     * @var string
     */
    private $optionalStringParameter;

    /**
     * @var int
     */
    private $optionalIntegerParameter;

    /**
     * @var TestAssetInterface
     */
    private $optionalInterfaceParameter;

    /**
     * @var TestAssetInterface
     */
    private $requiredInterfaceParam;

    /**
     * @var Basic
     */
    private $requiredObjectParameter;

    /**
     * @param TestAssetInterface $requiredInterfaceParameter
     * @param Basic $requiredObjectParameter
     * @param TestAssetInterface $optionalInterfaceParameter
     * @param Basic $optionalObjectParameter
     * @param string $optionalStringParameter
     * @param int $optionalIntegerParameter
     */
    public function __construct(
        TestAssetInterface $requiredInterfaceParameter,
        Basic $requiredObjectParameter,
        TestAssetInterface $optionalInterfaceParameter = null,
        Basic $optionalObjectParameter = null,
        $optionalStringParameter = self::CONSTRUCTOR_STRING_PARAM_DEFAULT,
        $optionalIntegerParameter = self::CONSTRUCTOR_INT_PARAM_DEFAULT
    ) {
        $this->optionalObjectParameter = $optionalObjectParameter;
        $this->optionalStringParameter = $optionalStringParameter;
        $this->optionalIntegerParameter = $optionalIntegerParameter;
        $this->optionalInterfaceParameter = $optionalInterfaceParameter;
        $this->requiredInterfaceParam = $requiredInterfaceParameter;
        $this->requiredObjectParameter = $requiredObjectParameter;
    }

    /**
     * @return Basic
     */
    public function getOptionalObjectParameter()
    {
        return $this->optionalObjectParameter;
    }

    /**
     * @return string
     */
    public function getOptionalStringParameter()
    {
        return $this->optionalStringParameter;
    }

    /**
     * @return int
     */
    public function getOptionalIntegerParameter()
    {
        return $this->optionalIntegerParameter;
    }

    /**
     * @return TestAssetInterface
     */
    public function getOptionalInterfaceParameter()
    {
        return $this->optionalInterfaceParameter;
    }

    /**
     * @return TestAssetInterface
     */
    public function getRequiredInterfaceParam()
    {
        return $this->requiredInterfaceParam;
    }

    /**
     * @return Basic
     */
    public function getRequiredObjectParameter()
    {
        return $this->requiredObjectParameter;
    }
}
