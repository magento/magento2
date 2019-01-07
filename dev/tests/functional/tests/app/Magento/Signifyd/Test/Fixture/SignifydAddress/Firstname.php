<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Signifyd\Test\Fixture\SignifydAddress;

use Magento\Mtf\Fixture\DataSource;

/**
 * Source handler for `firstname` field in shipping address fixture.
 */
class Firstname extends DataSource
{
    /**
     * @param string $data
     */
    public function __construct($data = '')
    {
        $this->data = $data;
    }

    /**
     * Add isolation for `firstname` field.
     *
     * @param null $key
     * @return string
     */
    public function getData($key = null)
    {
        $this->data = str_replace('%signifyd_isolation%', $this->generateIsolation(), $this->data);

        return parent::getData($key);
    }

    /**
     * Generates character isolation.
     *
     * @param int $length
     * @return string
     */
    private function generateIsolation($length = 10)
    {
        return substr(str_shuffle(str_repeat("abcdefghijklmnopqrstuvwxyz", $length)), 0, $length);
    }
}
