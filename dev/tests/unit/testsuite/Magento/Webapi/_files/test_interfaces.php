<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Module\Service;

/**
 * The list of test interfaces.
 */
interface FooV1Interface
{
    public function someMethod();
}
interface BarV1Interface
{
    public function someMethod();
}
interface FooBarV1Interface
{
    public function someMethod();
}
namespace Magento\Framework\Module\Service\Foo;

interface BarV1Interface
{
    public function someMethod();
}
