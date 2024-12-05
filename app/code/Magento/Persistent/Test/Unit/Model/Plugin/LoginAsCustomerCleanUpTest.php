<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Persistent\Test\Unit\Model\Plugin;

use Magento\Persistent\Model\Plugin\LoginAsCustomerCleanUp;
use Magento\Persistent\Helper\Session as PersistentSession;
use Magento\LoginAsCustomerApi\Api\AuthenticateCustomerBySecretInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class LoginAsCustomerCleanUpTest extends TestCase
{
    /**
     * @var LoginAsCustomerCleanUp
     */
    protected $plugin;

    /**
     * @var MockObject
     */
    protected $subjectMock;

    /**
     * @var MockObject
     */
    protected $persistentSessionMock;

    /**
     * @var MockObject
     */
    protected $persistentSessionModelMock;

    protected function setUp(): void
    {
        $this->persistentSessionMock = $this->createMock(PersistentSession::class);
        $this->persistentSessionModelMock = $this->createMock(\Magento\Persistent\Model\Session::class);
        $this->persistentSessionMock->method('getSession')->willReturn($this->persistentSessionModelMock);
        $this->subjectMock = $this->createMock(AuthenticateCustomerBySecretInterface::class);
        $this->plugin = new LoginAsCustomerCleanUp($this->persistentSessionMock);
    }

    public function testBeforeExecute()
    {
        $this->persistentSessionMock->expects($this->once())->method('isPersistent')->willReturn(true);
        $this->persistentSessionModelMock->expects($this->once())->method('removePersistentCookie');
        $result = $this->plugin->afterExecute($this->subjectMock);
        $this->assertEquals(null, $result);
    }
}
