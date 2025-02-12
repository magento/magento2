<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Jwt\Test\Unit\Header;

use Magento\Framework\Jwt\Header\X509Sha256Thumbprint;
use PHPUnit\Framework\TestCase;

class X509Sha256ThumbprintTest extends TestCase
{
    public function testGetValue(): void
    {
        $model = new X509Sha256Thumbprint('cert:=cert');

        $this->assertEquals('Y2VydDo9Y2VydA', $model->getValue());
    }
}
