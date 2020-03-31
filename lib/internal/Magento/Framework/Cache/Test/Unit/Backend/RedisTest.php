<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Cache\Test\Unit\Backend;

class RedisTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\Cache\Backend\Redis
     */
    private $model;

    /**
     * @var \Credis_Client|\PHPUnit\Framework\MockObject\MockObject
     */
    private $clientMock;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        $options = [
            'server' => '127.0.0.1',
            'port' => 1111,
        ];
        $this->model = new \Magento\Framework\Cache\Backend\Redis($options);

        $clientMock = $this->createMock(\Credis_Client::class);
        $this->clientMock = $clientMock;
        \Closure::bind(function () use ($clientMock) {
            // phpstan:ignore "Access to protected property"
            $this->_redis = $clientMock;
        }, $this->model, get_class($this->model))->__invoke();
    }

    /**
     * @return void
     */
    public function testSaveWithError(): void
    {
        $this->clientMock->method('__call')
            ->willThrowException(new \CredisException('Error'));
        $this->assertFalse($this->model->save('test-data', 'test-key'));
    }

    /**
     * @return void
     */
    public function testRemoveWithError(): void
    {
        $this->clientMock->method('__call')
            ->willThrowException(new \CredisException('Error'));
        $this->assertFalse($this->model->remove('test-key'));
    }
}
