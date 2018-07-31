<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\App\Test\Unit\Request;

use Magento\Framework\App\Request\HttpMethodMap;
use PHPUnit\Framework\TestCase;

class HttpMethodMapTest extends TestCase
{
    public function testFilter()
    {
        $map = new HttpMethodMap(
            ['method1' => '\\Throwable', 'method2' => 'DateTime']
        );
        $this->assertEquals(
            ['method1' => \Throwable::class, 'method2' => \DateTime::class],
            $map->getMap()
        );
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testExisting()
    {
        new HttpMethodMap(['method1' => 'NonExistingClass']);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testMethod()
    {
        new HttpMethodMap([\Throwable::class]);
    }
}
