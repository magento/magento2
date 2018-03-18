<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Unit\Model\Connector\ResponseHandler;

use Magento\Analytics\Model\Connector\ResponseHandler\Update;

<<<<<<< HEAD
=======
/**
 * Class UpdateTest
 */
>>>>>>> upstream/2.2-develop
class UpdateTest extends \PHPUnit\Framework\TestCase
{
    public function testHandleResult()
    {
        $updateHandler = new Update();
        $this->assertTrue($updateHandler->handleResponse([]));
    }
}
