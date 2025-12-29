<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Persistent\Test\Unit\Block\Header;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Helper\View;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Persistent\Block\Header\Additional;
use Magento\Persistent\Helper\Data;
use Magento\Persistent\Helper\Session;
use Magento\Persistent\Model\Session as PersistentSession;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Test for Additional block
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AdditionalTest extends TestCase
{
    use MockCreationTrait;

    /**
     * @var View|MockObject
     */
    private View|MockObject $customerViewHelperMock;

    /**
     * @var Session|MockObject
     */
    private Session|MockObject $persistentSessionHelperMock;

    /**
     * Customer repository
     *
     * @var CustomerRepositoryInterface|MockObject
     */
    private CustomerRepositoryInterface|MockObject $customerRepositoryMock;

    /**
     * @var Json|MockObject
     */
    private Json|MockObject $jsonSerializerMock;

    /**
     * @var Data|MockObject
     */
    private Data|MockObject $persistentHelperMock;

    /**
     * @var Additional|MockObject
     */
    private Additional|MockObject $additional;

    /**
     * Set up
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->customerViewHelperMock = $this->createMock(View::class);
        $this->persistentSessionHelperMock = $this->createPartialMock(
            Session::class,
            ['getSession']
        );
        $this->customerRepositoryMock = $this->createMock(CustomerRepositoryInterface::class);
        $this->jsonSerializerMock = $this->createPartialMock(
            Json::class,
            ['serialize']
        );
        $this->persistentHelperMock = $this->createPartialMock(
            Data::class,
            ['getLifeTime']
        );

        // Create a partial mock of Additional class without invoking constructor
        // This avoids ObjectManager::getInstance() calls in parent constructors
        $this->additional = $this->getMockBuilder(Additional::class)
            ->disableOriginalConstructor()
            ->onlyMethods([])
            ->getMock();

        $reflection = new \ReflectionClass(Additional::class);

        $customerViewHelperProperty = $reflection->getProperty('_customerViewHelper');
        $customerViewHelperProperty->setValue($this->additional, $this->customerViewHelperMock);

        $persistentSessionHelperProperty = $reflection->getProperty('_persistentSessionHelper');
        $persistentSessionHelperProperty->setValue($this->additional, $this->persistentSessionHelperMock);

        $customerRepositoryProperty = $reflection->getProperty('customerRepository');
        $customerRepositoryProperty->setValue($this->additional, $this->customerRepositoryMock);

        $jsonSerializerProperty = $reflection->getProperty('jsonSerializer');
        $jsonSerializerProperty->setValue($this->additional, $this->jsonSerializerMock);

        $persistentHelperProperty = $reflection->getProperty('persistentHelper');
        $persistentHelperProperty->setValue($this->additional, $this->persistentHelperMock);
    }

    /**
     * Test getCustomerId method
     *
     * @return void
     */
    public function testGetCustomerId(): void
    {
        $customerId = 1;
        $sessionMock = $this->createPartialMockWithReflection(
            PersistentSession::class,
            ['getCustomerId']
        );
        $sessionMock->expects($this->once())
            ->method('getCustomerId')
            ->willReturn($customerId);
        $this->persistentSessionHelperMock->expects($this->once())
            ->method('getSession')
            ->willReturn($sessionMock);

        $this->assertEquals($customerId, $this->additional->getCustomerId());
    }

    /**
     * Test getConfig method
     *
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
