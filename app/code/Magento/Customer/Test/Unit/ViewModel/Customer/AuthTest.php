<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\ViewModel\Customer;

use Magento\Customer\ViewModel\Customer\Auth;
use Magento\Framework\App\Http\Context;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AuthTest extends TestCase
{
    /**
     * @var Context|MockObject
     */
    private mixed $contextMock;

    /**
     * @var Auth
     */
    private Auth $model;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->contextMock = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new Auth(
            $this->contextMock
        );
        parent::setUp();
    }

    /**
     * Test is logged in value.
     *
     * @return void
     */
    public function testIsLoggedIn(): void
    {
        $this->contextMock->expects($this->once())
            ->method('getValue')
            ->willReturn(true);

        $this->assertEquals(
            true,
            $this->model->isLoggedIn()
        );
    }
}
