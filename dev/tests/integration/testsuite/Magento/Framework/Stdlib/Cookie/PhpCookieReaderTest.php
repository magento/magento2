<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

// @codingStandardsIgnoreFile

namespace Magento\Framework\Stdlib\Cookie;

use Magento\Framework\Stdlib\Cookie\PhpCookieReader;

class PhpCookieReaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var array
     */
    protected $preTestCookies;

    /**
     * @var PhpCookieReader
     */
    protected $model;

    const NAME = 'cookie-name';
    const VALUE = 'cookie-val';
    const DEFAULT_VAL = 'default-val';

    public function setUp()
    {
        $this->preTestCookies = $_COOKIE;
        $_COOKIE = [];
        $_COOKIE[self::NAME] = self::VALUE;
        $this->model = new PhpCookieReader();
    }
    public function testGetCookieExists()
    {
        $this->assertSame(self::VALUE, $this->model->getCookie(self::NAME, self::DEFAULT_VAL));
    }

    public function testGetCookieDefault()
    {
        $this->assertSame(self::DEFAULT_VAL, $this->model->getCookie('cookies does not exist', self::DEFAULT_VAL));
        $this->assertSame(self::DEFAULT_VAL, $this->model->getCookie(null, self::DEFAULT_VAL));
    }

    public function testGetCookieNoDefault()
    {
        $this->assertNull($this->model->getCookie('cookies does not exist'));
        $this->assertNull($this->model->getCookie(null));
    }

    public function tearDown()
    {
        $_COOKIE = $this->preTestCookies;
    }
}
