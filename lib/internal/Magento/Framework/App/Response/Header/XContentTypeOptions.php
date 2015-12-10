<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Response\Header;

use Magento\Framework\App\Response\Header\AbstractHeader;

class XContentTypeOptions extends AbstractHeader
{
    protected $value = 'nosniff';
    protected $name = 'X-Content-Type-Options';
}
