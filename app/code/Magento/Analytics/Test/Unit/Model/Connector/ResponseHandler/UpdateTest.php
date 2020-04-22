<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Analytics\Test\Unit\Model\Connector\ResponseHandler;

use Magento\Analytics\Model\Connector\ResponseHandler\Update;
use PHPUnit\Framework\TestCase;

class UpdateTest extends TestCase
{
    public function testHandleResult()
    {
        $updateHandler = new Update();
        $this->assertTrue($updateHandler->handleResponse([]));
    }
}
