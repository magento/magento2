<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\Session;

use Magento\Backend\Model\SessionTest;

function session_name($name)
{
    SessionTest::assertEquals($name, 'adminhtml');
}
