<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Captcha\Test\Fixture\User;

use Magento\Mtf\Fixture\DataSource;

/**
 * Prepare captcha.
 */
class Captcha extends DataSource
{
    /**
     * Rough fixture field data.
     *
     * @var array|null
     */
    private $fixtureData = null;

    /**
     * @constructor
     * @param array $data
     */
    public function __construct(
        $data = []
    ) {
        $this->fixtureData = $data;
        $this->data = $data;
    }

    /**
     * Return prepared data set.
     *
     * @param string $key [optional]
     * @return mixed
     */
    public function getData($key = null)
    {
        return parent::getData($key);
    }

    /**
     * Return website code.
     *
     * @return Website
     */
    public function getCaptcha()
    {
        return $this->data;
    }
}
