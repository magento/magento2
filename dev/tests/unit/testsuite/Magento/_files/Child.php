<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Test\Di;

require_once __DIR__ . '/DiParent.php';
require_once __DIR__ . '/ChildInterface.php';
class Child extends \Magento\Test\Di\DiParent implements \Magento\Test\Di\ChildInterface
{
}
