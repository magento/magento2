<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Persistent\Test\Unit\Block\Header;

/**
 * Class AdditionalTest
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class AdditionalTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Customer\Helper\View|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $customerViewHelperMock;

    /**
     * @var \Magento\Persistent\Helper\Session|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $persistentSessionHelperMock;

    /**
     * Customer repository
     *
     * @var \Magento\Customer\Api\CustomerRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $customerRepositoryMock;

    /**
     * @var \Magento\Framework\View\Element\Template\Context|\PHPUnit\Framework\MockObject\MockObject
     */
    protected $contextMock;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json|\PHPUnit\Framework\MockObject\MockObject
     */
    private $jsonSerializerMock;

    /**
     * @var \Magento\Persistent\Helper\Data|\PHPUnit\Framework\MockObject\MockObject
     */
    private $persistentHelperMock;

    /**
     * @var \Magento\Persistent\Block\Header\Additional
     */
    protected $additional;

    /**
     * @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager
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
        $this->objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);

        $this->contextMock = $this->createPartialMock(\Magento\Framework\View\Element\Template\Context::class, []);
        $this->customerViewHelperMock = $this->createMock(\Magento\Customer\Helper\View::class);
        $this->persistentSessionHelperMock = $this->createPartialMock(
            \Magento\Persistent\Helper\Session::class,
            ['getSession']
        );
        $this->customerRepositoryMock = $this->getMockForAbstractClass(
            \Magento\Customer\Api\CustomerRepositoryInterface::class,
            [],
            '',
            false,
            true,
            true,
            ['getById']
        );

        $this->jsonSerializerMock = $this->createPartialMock(
            \Magento\Framework\Serialize\Serializer\Json::class,
            ['serialize']
        );
        $this->persistentHelperMock = $this->createPartialMock(
            \Magento\Persistent\Helper\Data::class,
            ['getLifeTime']
        );

        $this->additional = $this->objectManager->getObject(
            \Magento\Persistent\Block\Header\Additional::class,
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
        /** @var \Magento\Persistent\Model\Session|\PHPUnit\Framework\MockObject\MockObject $sessionMock */
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
