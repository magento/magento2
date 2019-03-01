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
     * @var \Magento\Customer\Helper\View|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerViewHelperMock;

    /**
     * @var \Magento\Persistent\Helper\Session|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $persistentSessionHelperMock;

    /**
     * Customer repository
     *
     * @var \Magento\Customer\Api\CustomerRepositoryInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $customerRepositoryMock;

    /**
     * @var \Magento\Framework\View\Element\Template\Context|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $contextMock;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json|\PHPUnit_Framework_MockObject_MockObject
     */
    private $jsonSerializerMock;

    /**
     * @var \Magento\Persistent\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
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
    protected function setUp()
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
    public function testGetCustomerId()
    {
        $customerId = 1;
        /** @var \Magento\Persistent\Model\Session|\PHPUnit_Framework_MockObject_MockObject $sessionMock */
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
    public function testGetConfig()
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
