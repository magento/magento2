<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Webapi\Controller\Rest;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Webapi\Model\Config\Converter;

/**
 * Override parameter values
 */
class ParamsOverrider
{
    /**
     * @var \Magento\Authorization\Model\UserContextInterface
     */
    protected $userContext;

    /**
     * Initialize dependencies
     *
     * @param UserContextInterface $userContext
     */
    public function __construct(UserContextInterface $userContext)
    {
        $this->userContext = $userContext;
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
                if ($paramData[Converter::KEY_VALUE] == '%customer_id%'
                    && $this->userContext->getUserType() === UserContextInterface::USER_TYPE_CUSTOMER
                ) {
                    $value = $this->userContext->getUserId();
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
