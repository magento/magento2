<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Persistent\Test\Unit\Block\Header;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Template\Context;
use Magento\Persistent\Block\Header\Additional;
use Magento\Persistent\Helper\Data;
use Magento\Persistent\Helper\Session;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AdditionalTest extends TestCase
{
    /**
     * @var Additional
     */
    private $additional;

    /**
     * @var Session|MockObject
     */
    private $persistentSessionHelperMock;

    /**
     * @var Json|MockObject
     */
    private $jsonSerializerMock;

    /**
     * @var Data|MockObject
     */
    private $persistentHelperMock;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp(): void
    {
        /** @var Context|MockObject $contextMock */
        $contextMock = $this->createPartialMock(Context::class, []);
        $this->persistentSessionHelperMock = $this->createPartialMock(
            Session::class,
            ['getSession']
        );

        $this->jsonSerializerMock = $this->createPartialMock(
            Json::class,
            ['serialize']
        );
        $this->persistentHelperMock = $this->createPartialMock(
            Data::class,
            ['getLifeTime']
        );

        $this->additional = new Additional(
            $contextMock,
            $this->persistentSessionHelperMock,
            $this->jsonSerializerMock,
            $this->persistentHelperMock
        );
    }

    /**
     * @return void
     */
    public function testGetCustomerId(): void
    {
        $customerId = 1;
        /** @var \Magento\Persistent\Model\Session|MockObject $sessionMock */
        $sessionMock = $this->createPartialMock(\Magento\Persistent\Model\Session::class, ['getCustomerId']);
        $sessionMock->expects($this->once())
            ->method('getCustomerId')
            ->willReturn($customerId);
        $this->persistentSessionHelperMock->expects($this->once())
            ->method('getSession')
            ->willReturn($sessionMock);

        $this->assertEquals($customerId, $this->additional->getCustomerId());
    }

    /**
     * @return void
     */
    public function testGetConfig(): void
    {
        $lifeTime = 500;
        $arrayToSerialize = ['expirationLifetime' => $lifeTime];
        $serializedArray = '{"expirationLifetime":' . $lifeTime . '}';

        $this->persistentHelperMock->expects($this->once())
            ->method('getLifeTime')
            ->willReturn($lifeTime);
        $this->jsonSerializerMock->expects($this->once())
            ->method('serialize')
            ->with($arrayToSerialize)
            ->willReturn($serializedArray);

        $this->assertEquals($serializedArray, $this->additional->getConfig());
    }
}
