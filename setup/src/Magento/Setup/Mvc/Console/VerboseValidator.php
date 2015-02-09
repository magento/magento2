<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Mvc\Console;

use Magento\Setup\Controller\ConsoleController;

/**
 * Validator for checking parameters in CLI
 */
class VerboseValidator
{
    /**
     * Checks parameters and returns validation messages
     *
     * @param array $data
     * @param array $config
     * @return string
     */
    public function validate(array $data, array $config)
    {
        $validationMessages = '';
        $userAction = null;
        if (!empty($data)) {
            $userAction = $data[0];
            array_shift($data);
        }
        if (isset($userAction) && isset($config[$userAction])) {
            // parse the expected parameters of the action
            $matcher = new RouteMatcher($config[$userAction]['options']['route']);
            $parts = $matcher->getParts();
            array_shift($parts);
            $expectedParams = [];
            foreach ($parts as $part) {
                $expectedParams[$part['name']] = $part;
            }
            // parse user parameters
            $userParams = $this->parseUserParams($data);

            $missingParams = $this->checkMissingParameter($expectedParams, $userParams);
            $extraParams = $this->checkExtraParameter($expectedParams, $userParams);
            $missingValues = $this->checkMissingValue($expectedParams, $userParams);
            $extraValues = $this->checkExtraValue($expectedParams, $userParams);

            $validationMessages = PHP_EOL;

            if (!empty($missingParams)) {
                $validationMessages .= 'Missing required parameters:' . PHP_EOL;
                foreach ($missingParams as $missingParam) {
                    $validationMessages .= $missingParam . PHP_EOL;
                }
                $validationMessages .= PHP_EOL;
            }
            if (!empty($extraParams)) {
                $validationMessages .= 'Unidentified parameters:' . PHP_EOL;
                foreach ($extraParams as $extraParam) {
                    $validationMessages .= $extraParam . PHP_EOL;
                }
                $validationMessages .= PHP_EOL;
            }
            if (!empty($missingValues)) {
                $validationMessages .= 'Parameters missing value:' . PHP_EOL;
                foreach ($missingValues as $missingValue) {
                    $validationMessages .= $missingValue . PHP_EOL;
                }
                $validationMessages .= PHP_EOL;
            }
            if (!empty($extraValues)) {
                $validationMessages .= 'Parameters that don\'t need value:' . PHP_EOL;
                foreach ($extraValues as $extraValue) {
                    $validationMessages .= $extraValue . PHP_EOL;
                }
                $validationMessages .= PHP_EOL;
            }
            if (empty($missingParams) && empty($extraParams) && empty($missingValues) && empty($extraValue)) {
                $validationMessages .= 'Please make sure parameters are in correct format and are not repeated.';
                $validationMessages .= PHP_EOL . PHP_EOL;
            }

            // add usage message
            $usages = ConsoleController::getCommandUsage();
            $validationMessages .= 'Usage:' . PHP_EOL . "{$userAction} ";
            $validationMessages .= $usages[$userAction] . PHP_EOL . PHP_EOL;

        } else {
            if (!is_null($userAction)) {
                $validationMessages .= PHP_EOL . "Unknown action name '{$userAction}'." . PHP_EOL . PHP_EOL;
            } else {
                $validationMessages .= PHP_EOL . "No action is given in the command." . PHP_EOL . PHP_EOL;
            }
            $validationMessages .= 'Available options: ' . PHP_EOL;
            foreach (array_keys($config) as $action) {
                $validationMessages .= $action . PHP_EOL;
            }
            $validationMessages .= PHP_EOL;
        }

        return $validationMessages;
    }

    /**
     * Parse user input
     *
     * @param array $content
     * @return array
     */
    private function parseUserParams(array $content)
    {
        $parameters = [];
        foreach ($content as $param) {
            $parsed = explode('=', $param, 2);
            $value = isset($parsed[1]) ? $parsed[1] : '';
            if (strpos($parsed[0], '--') !== false) {
                $key = substr($parsed[0], 2, strlen($parsed[0]) - 2);
            } else {
                $key = $parsed[0];
            }

            $parameters[$key] = $value;
        }
        return $parameters;
    }
    /**
     * Check for any missing parameters
     *
     * @param array $expectedParams
     * @param array $actualParams
     * @return array
     */
    public function checkMissingParameter($expectedParams, $actualParams)
    {
        $missingParams = array_diff(array_keys($expectedParams), array_keys($actualParams));
        foreach ($missingParams as $key => $missingParam) {
            /* disregard if optional parameter */
            if (!$expectedParams[$missingParam]['required']) {
                unset($missingParams[$key]);
            }
        }
        // some parameters have alternative names, verify user input with theses alternative names
        foreach ($missingParams as $key => $missingParam) {
            foreach (array_keys($actualParams) as $actualParam) {
                if (isset($expectedParams[$missingParam]['alternatives'])) {
                    foreach ($expectedParams[$missingParam]['alternatives'] as $alternative) {
                        if ($actualParam === $alternative) {
                            unset($missingParams[$key]);
                            break 2;
                        }
                    }
                }
            }
        }
        return $missingParams;
    }

    /**
     * Check for any extra parameters
     *
     * @param array $expectedParams
     * @param array $actualParams
     * @return array
     */
    public function checkExtraParameter($expectedParams, $actualParams)
    {
        $extraParams = array_diff(array_keys($actualParams), array_keys($expectedParams));
        // some parameters have alternative names, make sure $extraParams doesn't contain these alternatives names
        foreach ($extraParams as $key => $extraParam) {
            foreach ($expectedParams as $expectedParam) {
                if (isset($expectedParam['alternatives'])) {
                    foreach ($expectedParam['alternatives'] as $alternative) {
                        if ($extraParam === $alternative) {
                            unset($extraParams[$key]);
                            break 2;
                        }
                    }
                }
            }
        }
        return $extraParams;
    }

    /**
     * Checks for parameters that are missing values
     *
     * @param array $expectedParams
     * @param array $actualParams
     * @return array
     */
    public function checkMissingValue($expectedParams, $actualParams)
    {
        $missingValues = [];
        foreach ($actualParams as $param => $value) {
            if (isset($expectedParams[$param])) {
                if ($value === '' && $expectedParams[$param]['hasValue']) {
                    $missingValues[] = $param;
                }
            }
        }
        return $missingValues;
    }

    /**
     * Checks for parameters that do not need values
     *
     * @param array $expectedParams
     * @param array $actualParams
     * @return array
     */
    public function checkExtraValue($expectedParams, $actualParams)
    {
        $extraValues = [];
        foreach ($actualParams as $param => $value) {
            if (isset($expectedParams[$param])) {
                if ($value !== '' && !$expectedParams[$param]['hasValue']) {
                    $extraValues[] = $param;
                }
            }
        }
        return $extraValues;
    }
}
