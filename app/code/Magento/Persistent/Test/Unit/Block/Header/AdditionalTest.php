<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Persistent\Test\Unit\Block\Header;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Helper\View;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Element\Template\Context;
use Magento\Persistent\Block\Header\Additional;
use Magento\Persistent\Helper\Data;
use Magento\Persistent\Helper\Session;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AdditionalTest extends TestCase
{
    /**
     * @var View|MockObject
     */
    protected $customerViewHelperMock;

    /**
     * @var Session|MockObject
     */
    protected $persistentSessionHelperMock;

    /**
     * Customer repository
     *
     * @var CustomerRepositoryInterface|MockObject
     */
    protected $customerRepositoryMock;

    /**
     * @var Context|MockObject
     */
    protected $contextMock;

    /**
     * @var Json|MockObject
     */
    private $jsonSerializerMock;

    /**
     * @var Data|MockObject
     */
    private $persistentHelperMock;

    /**
     * @var Additional
     */
    protected $additional;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * Set up
     *
     * @return void
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function setUp(): void
    {
        $this->objectManager = new ObjectManager($this);

        $this->contextMock = $this->createPartialMock(Context::class, []);
        $this->customerViewHelperMock = $this->createMock(View::class);
        $this->persistentSessionHelperMock = $this->createPartialMock(
            Session::class,
            ['getSession']
        );
        $this->customerRepositoryMock = $this->getMockForAbstractClass(
            CustomerRepositoryInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getById']
        );

        $this->jsonSerializerMock = $this->createPartialMock(
            Json::class,
            ['serialize']
        );
        $this->persistentHelperMock = $this->createPartialMock(
            Data::class,
            ['getLifeTime']
        );

        $this->additional = $this->objectManager->getObject(
            Additional::class,
            [
                'context' => $this->contextMock,
                'customerViewHelper' => $this->customerViewHelperMock,
                'persistentSessionHelper' => $this->persistentSessionHelperMock,
                'customerRepository' => $this->customerRepositoryMock,
                'data' => [],
                'jsonSerializer' => $this->jsonSerializerMock,
                'persistentHelper' => $this->persistentHelperMock,
            ]
        );
    }

    /**
     * @return void
     */
    public function testGetCustomerId(): void
    {
        $customerId = 1;
        /** @var \Magento\Persistent\Model\Session|MockObject $sessionMock */
        $sessionMock = $this->getMockBuilder(\Magento\Persistent\Model\Session::class)->addMethods(['getCustomerId'])
            ->disableOriginalConstructor()
            ->getMock();
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
