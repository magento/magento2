<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Response;

class Headers extends \Zend\Http\Headers
{
    /**
     * Normalize a header name
     *
     * Normalizes a header name to X-Capitalized-Names
     *
     * @param  string $name
     * @return string
     */
    protected function normalizeHeader($name)
    {
        $filtered = str_replace(['-', '_'], ' ', (string)$name);
        $filtered = ucwords(strtolower($filtered));
        $filtered = str_replace(' ', '-', $filtered);
        return $filtered;
    }

    /**
     * Get all headers of a certain name/type
     *
     * @param  string $name
     * @return bool|\Zend\Http\Header\HeaderInterface|\ArrayIterator
     */
    public function get($name)
    {
        $name = $this->normalizeHeader($name);
        return parent::get($name);
    }

    /**
     * Test for existence of a type of header
     *
     * @param  string $name
     * @return bool
     */
    public function has($name)
    {
        $name = $this->normalizeHeader($name);
        return parent::has($name);
    }
}