<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
        $this->_helper = $objectManager->get('Magento\Framework\HTTP\PhpEnvironment\RemoteAddress');
    }

    public function testGetRemoteAddress()
    {
        $this->assertEquals(false, $this->_helper->getRemoteAddress());
    }
}
