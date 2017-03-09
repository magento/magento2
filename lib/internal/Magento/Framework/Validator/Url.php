<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Validator;

/**
 * Class Url validates URL and checks that it has allowed scheme
 */
class Url
{
    /**
     * Validate URL and check that it has allowed scheme
     *
     * @param string $value
     * @param array $allowedSchemes
     * @return bool
     */
    public function isValid($value, array $allowedSchemes = [])
    {
        $isValid = true;

        if (!filter_var($value, FILTER_VALIDATE_URL)) {
            $isValid = false;
        }

        if ($isValid && !empty($allowedSchemes)) {
            $url = parse_url($value);
            if (empty($url['scheme']) || !in_array($url['scheme'], $allowedSchemes)) {
                $isValid = false;
            }
        }

        return $isValid;
    }
}
