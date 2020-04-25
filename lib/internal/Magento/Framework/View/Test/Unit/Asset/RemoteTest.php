<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Test\Unit\Asset;

use PHPUnit\Framework\TestCase;
use Magento\Framework\View\Asset\Remote;

class RemoteTest extends TestCase
{
    /**
     * @var Remote
     */
    protected $_object;

    protected function setUp(): void
    {
        $this->_object = new Remote('https://127.0.0.1/magento/test/style.css', 'css');
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
