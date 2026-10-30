<?php
/**
 * Copyright 2021 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Framework\Jwt\Test\Unit\Header;

use Magento\Framework\Jwt\Header\X509Chain;
use PHPUnit\Framework\TestCase;

class X509ChainTest extends TestCase
{
    public function testEmpty(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new X509Chain([]);
    }

    public function testGetValue(): void
    {
        $model = new X509Chain(['cert1', 'cert2']);

        $this->assertEquals('["Y2VydDE=","Y2VydDI="]', $model->getValue());
    }
}
