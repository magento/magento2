<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test;

class ResponseTest extends \PHPUnit_Framework_TestCase
{
    public function testCanSendHeaders()
    {
        $response = new \Magento\TestFramework\Response(
            $this->getMock('Magento\Framework\Stdlib\CookieManagerInterface'),
            $this->getMock('Magento\Framework\Stdlib\Cookie\CookieMetadataFactory', [], [], '', false),
            $this->getMock('Magento\Framework\App\Http\Context', [], [], '', false)
        );
        $this->assertTrue($response->canSendHeaders());
        $this->assertTrue($response->canSendHeaders(false));
    }
}
