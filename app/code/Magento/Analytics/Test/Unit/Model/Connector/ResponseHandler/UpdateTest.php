<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\Test\Unit\Model\Connector\ResponseHandler;

use Magento\Analytics\Model\Connector\ResponseHandler\Update;

<<<<<<< HEAD
/**
 * Class UpdateTest
 */
=======
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
class UpdateTest extends \PHPUnit\Framework\TestCase
{
    public function testHandleResult()
    {
        $updateHandler = new Update();
        $this->assertTrue($updateHandler->handleResponse([]));
    }
}
