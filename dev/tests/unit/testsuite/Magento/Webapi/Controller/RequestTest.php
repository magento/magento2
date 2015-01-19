<?php
/**
 * \Magento\Webapi\Controller\Request
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Webapi\Controller;


use Magento\TestFramework\Helper\ObjectManager;

class RequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Webapi\Controller\Request
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
            'Magento\Webapi\Controller\Request',
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
