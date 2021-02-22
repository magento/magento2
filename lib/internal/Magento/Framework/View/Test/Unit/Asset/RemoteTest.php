<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit\Asset;

class RemoteTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\View\Asset\Remote
     */
    protected $_object;

    protected function setUp(): void
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
