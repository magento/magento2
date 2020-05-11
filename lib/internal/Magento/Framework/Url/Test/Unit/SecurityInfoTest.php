<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Url\Test\Unit;

use Magento\Framework\Url\SecurityInfo;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class SecurityInfoTest extends TestCase
{
    /**
     * @var MockObject
     */
    protected $_scopeConfigMock;

    /**
     * @var SecurityInfo
     */
    protected $_model;

    protected function setUp(): void
    {
        $this->_model = new SecurityInfo(['/account', '/cart'], ['/cart/remove', 'customer']);
    }

    /**
     * @param string $url
     * @param bool $expected
     * @dataProvider secureUrlDataProvider
     */
    public function testIsSecureChecksIfUrlIsInSecureList($url, $expected)
    {
        $this->assertEquals($expected, $this->_model->isSecure($url));
    }

    /**
     * @return array
     */
    public function secureUrlDataProvider()
    {
        return [
            ['/account', true],
            ['/product', false],
            ['/product/12312', false],
            ['/cart', true],
            ['/cart/add', true],
            ['/cart/remove', false],
            ['/customer', false]
        ];
    }
}
