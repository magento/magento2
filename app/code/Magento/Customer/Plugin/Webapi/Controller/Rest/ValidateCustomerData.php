<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Plugin\Webapi\Controller\Rest;

use Magento\Webapi\Controller\Rest\ParamsOverrider;

/**
 * Validates Customer Data
 */
class ValidateCustomerData
{
    private const CUSTOMER_KEY = 'customer';

    /**
     * Before Overriding to validate data
     *
     * @param ParamsOverrider $subject
     * @param array $inputData
     * @param array $parameters
     * @return array[]
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeOverride(ParamsOverrider $subject, array $inputData, array $parameters): array
    {
        if (isset($inputData[self::CUSTOMER_KEY])) {
            $inputData[self::CUSTOMER_KEY] = $this->validateInputData($inputData[self::CUSTOMER_KEY]);
        }
        return [$inputData, $parameters];
    }

    /**
     * Validates InputData
     *
     * @param array $inputData
     * @return array
     */
    private function validateInputData(array $inputData): array
    {
        $result = [];

        $data = array_filter($inputData, function ($k) use (&$result) {
            $key = is_string($k) ? strtolower(str_replace('_', "", $k)) : $k;
            return !isset($result[$key]) && ($result[$key] = true);
        }, ARRAY_FILTER_USE_KEY);

        return array_map(function ($value) {
            return is_array($value) ? $this->validateInputData($value) : $value;
        }, $data);
    }
}
