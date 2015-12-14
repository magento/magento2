<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Response\HeaderProvider;

class XFrameOptionsTest extends AbstractHeaderTest
{
    public function testHeaderPresent()
    {
        parent::verifyHeader('X-Frame-Options', 'SAMEORIGIN');
    }
}
