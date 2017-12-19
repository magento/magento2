<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\PageCache\Test\Unit\Block\Cookie;

use \Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

/**
 * Frontend cookie prolongation model test class.
 * @covers \Magento\PageCache\Model\Cookie\Prolongation\Frontend
 */
class FrontendTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Stdlib\CookieManagerInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_cookieManagerMock;
    /**
     * @var \Magento\PageCache\Model\Cookie\Prolongation\Frontend
     */
    protected $_frontendCookieProlongation;

    /**
     * Initial setup.
     *
     * @return void
     */
    public function setUp()
    {
        $this->_cookieManagerMock = $this->createMock(\Magento\Framework\Stdlib\CookieManagerInterface::class);

        $this->_frontendCookieProlongation = (new ObjectManager($this))->getObject(
            \Magento\PageCache\Model\Cookie\Prolongation\Frontend::class,
            [
                'cookieManager' => $this->_cookieManagerMock
            ]
        );
    }

    /**
     * Tests execute() method.
     *
     * @param string $sessionId Session ID.
     *
     * @return void
     *
     * @dataProvider executeDataProvider
     */
    public function testExecuteWithSessionId($sessionId)
    {
        $this->_cookieManagerMock
            ->expects($this->atLeastOnce())
            ->method('getCookie')
            ->willReturn($sessionId);

        $this->_cookieManagerMock
            ->expects($this->once())
            ->method('setSensitiveCookie');

        $this->_frontendCookieProlongation->execute();
    }

    /**
     * Data provider for execute() method test.
     *
     * @return array
     */
    public function executeDataProvider()
    {
        return [
            'sessionId' => [
                'sessionId' => '1234567890'
            ]
        ];
    }

    /**
     * Tests execute() method (session ID is missed).
     *
     * @return void
     */
    public function testExecuteWithoutSessionId()
    {
        $this->_cookieManagerMock
            ->expects($this->once())
            ->method('getCookie')
            ->willReturn(null);

        $this->_cookieManagerMock
            ->expects($this->never())
            ->method('setSensitiveCookie');

        $this->_frontendCookieProlongation->execute();
    }
}