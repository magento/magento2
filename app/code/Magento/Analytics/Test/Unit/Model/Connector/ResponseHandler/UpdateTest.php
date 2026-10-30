<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
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
