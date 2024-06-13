<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\App\Response\HeaderProvider;

class XFrameOptionsTest extends AbstractHeaderTestCase
{
    public function testHeaderPresent()
    {
        $this->assertHeaderPresent('X-Frame-Options', 'SAMEORIGIN');
    }
}
