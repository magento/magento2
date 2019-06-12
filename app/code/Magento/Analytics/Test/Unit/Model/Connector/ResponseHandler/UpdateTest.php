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
>>>>>>> 57ffbd948415822d134397699f69411b67bcf7bc
class UpdateTest extends \PHPUnit\Framework\TestCase
{
    public function testHandleResult()
    {
        $updateHandler = new Update();
        $this->assertTrue($updateHandler->handleResponse([]));
    }
}
