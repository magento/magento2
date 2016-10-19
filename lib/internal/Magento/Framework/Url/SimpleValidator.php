<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Url;

class SimpleValidator
{
    /**
     * @var array
     */
    private $allowedSchemes = ['http', 'https'];

    /**
     * Set allowed Schemes
     *
     * @param array $allowedSchemes
     */
    public function setAllowedSchemes(array $allowedSchemes)
    {
        if (!empty($allowedSchemes)) {
            $this->allowedSchemes = $allowedSchemes;
        }
    }

    /**
     * Get allowed Schemes
     *
     * @return array
     */
    public function getAllowedSchemes()
    {
        return $this->allowedSchemes;
    }

    /**
     * Check that URL contains allowed symbols and use allowed scheme
     *
     * @param string $value
     * @return bool
     */
    public function isValid($value)
    {
        $isValid = true;

        // Check that URL contains allowed symbols
        if (!filter_var($value, FILTER_VALIDATE_URL)) {
            $isValid = false;
        }

        // Check that scheme there is in list of allowed schemes
        $url = parse_url($value);
        if (empty($url['scheme']) || !in_array($url['scheme'], $this->allowedSchemes)) {
            $isValid = false;
        }

        return $isValid;
    }
}
