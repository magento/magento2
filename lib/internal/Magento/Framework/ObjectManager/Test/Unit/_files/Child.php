<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Test\Di;

require_once __DIR__ . '/DiParent.php';
require_once __DIR__ . '/ChildInterface.php';
class Child extends DiParent implements ChildInterface
{
}
