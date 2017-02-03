<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Webapi\Controller\Rest;

use Magento\Framework\Webapi\Rest\Request\ParamOverriderInterface;
use Magento\Webapi\Model\Config\Converter;

/**
 * Override parameter values
 */
class ParamsOverrider
{
    /**
     * @var ParamOverriderInterface[]
     */
    private $paramOverriders;

    /**
     * Initialize dependencies
     *
     * @param ParamOverriderInterface[] $paramOverriders
     */
    public function __construct(
        array $paramOverriders = []
    ) {
        $this->paramOverriders = $paramOverriders;
    }

    /**
     * Override parameter values based on webapi.xml
     *
     * @param array $inputData Incoming data from request
     * @param array $parameters Contains parameters to replace or default
     * @return array Data in same format as $inputData with appropriate parameters added or changed
     */
    public function override(array $inputData, array $parameters)
    {
        foreach ($parameters as $name => $paramData) {
            $arrayKeys = explode('.', $name);
            if ($paramData[Converter::KEY_FORCE] || !$this->isNestedArrayValueSet($inputData, $arrayKeys)) {
                $paramValue = $paramData[Converter::KEY_VALUE];
                if (isset($this->paramOverriders[$paramValue])) {
                    $value = $this->paramOverriders[$paramValue]->getOverriddenValue();
                } else {
                    $value = $paramData[Converter::KEY_VALUE];
                }
                $this->setNestedArrayValue($inputData, $arrayKeys, $value);
            }
        }
        return $inputData;
    }

    /**
     * Determine if a nested array value is set.
     *
     * @param array &$nestedArray
     * @param string[] $arrayKeys
     * @return bool true if array value is set
     */
    protected function isNestedArrayValueSet(&$nestedArray, $arrayKeys)
    {
        $currentArray = &$nestedArray;

        foreach ($arrayKeys as $key) {
            if (!isset($currentArray[$key])) {
                return false;
            }
            $currentArray = &$currentArray[$key];
        }
        return true;
    }

    /**
     * Set a nested array value.
     *
     * @param array &$nestedArray
     * @param string[] $arrayKeys
     * @param string $valueToSet
     * @return void
     */
    protected function setNestedArrayValue(&$nestedArray, $arrayKeys, $valueToSet)
    {
        $currentArray = &$nestedArray;
        $lastKey = array_pop($arrayKeys);

        foreach ($arrayKeys as $key) {
            if (!isset($currentArray[$key])) {
                $currentArray[$key] = [];
            }
            $currentArray = &$currentArray[$key];
        }

        $currentArray[$lastKey] = $valueToSet;
    }
}
