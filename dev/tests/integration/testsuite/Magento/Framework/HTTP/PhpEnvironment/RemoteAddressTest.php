<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\HTTP\PhpEnvironment;

class RemoteAddressTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress
     */
    protected $_helper;

    protected function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->_helper = $objectManager->get(\Magento\Framework\HTTP\PhpEnvironment\RemoteAddress::class);
    }

    public function testGetRemoteAddress()
    {
        $this->assertEquals(false, $this->_helper->getRemoteAddress());
    }
}
