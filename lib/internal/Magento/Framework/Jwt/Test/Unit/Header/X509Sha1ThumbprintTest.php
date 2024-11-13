<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Jwt\Test\Unit\Header;

use Magento\Framework\Jwt\Header\X509Sha1Thumbprint;
use PHPUnit\Framework\TestCase;

class X509Sha1ThumbprintTest extends TestCase
{
    public function testGetValue(): void
    {
        $model = new X509Sha1Thumbprint('cert:==somecert');

        $this->assertEquals('Y2VydDo9PXNvbWVjZXJ0', $model->getValue());
    }
}
