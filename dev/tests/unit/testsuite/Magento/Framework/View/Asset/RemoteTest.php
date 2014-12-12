<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\View\Asset;

class RemoteTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\View\Asset\Remote
     */
    protected $_object;

    protected function setUp()
    {
        $this->_object = new \Magento\Framework\View\Asset\Remote('https://127.0.0.1/magento/test/style.css', 'css');
    }

    public function testGetUrl()
    {
        $this->assertEquals('https://127.0.0.1/magento/test/style.css', $this->_object->getUrl());
    }

    public function testGetContentType()
    {
        $this->assertEquals('css', $this->_object->getContentType());
    }
}
