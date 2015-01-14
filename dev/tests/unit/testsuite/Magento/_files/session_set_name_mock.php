<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Session;

use Magento\Backend\Model\SessionTest;

function session_name($name)
{
    SessionTest::assertEquals($name, 'adminhtml');
}
