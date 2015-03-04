<?php
/**
 * \Magento\Framework\Webapi
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Webapi;

use Magento\TestFramework\Helper\ObjectManager;

class RequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Webapi\Request
     */
    private $request;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieReaderInterface
     */
    private $cookieReader;

    public function setUp()
    {

        $objectManager = new ObjectManager($this);
        $this->cookieReader = $this->getMock('Magento\Framework\Stdlib\Cookie\CookieReaderInterface');

        $this->request = $objectManager->getObject(
            'Magento\Framework\Webapi\Request',
            ['cookieReader' => $this->cookieReader]
        );
    }

    public function testGetCookie()
    {
        $key = "cookieName";
        $default = "defaultValue";

        $this->cookieReader
            ->expects($this->once())
            ->method('getCookie')
            ->with($key, $default);

        $this->request->getCookie($key, $default);
    }
}
