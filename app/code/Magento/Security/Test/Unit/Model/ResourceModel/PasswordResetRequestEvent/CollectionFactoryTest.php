<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Security\Test\Unit\Model\ResourceModel\PasswordResetRequestEvent;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Security\Model\Config\Source\ResetMethod;
use Magento\Security\Model\ConfigInterface;
use Magento\Security\Model\ResourceModel\PasswordResetRequestEvent\CollectionFactory;
use Magento\Security\Model\ResourceModel\PasswordResetRequestEvent\Collection;

class CollectionFactoryTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Framework\ObjectManagerInterface | \PHPUnit_Framework_MockObject_MockObject */
    protected $objectManagerMock;

    /** @var ConfigInterface | \PHPUnit_Framework_MockObject_MockObject */
    protected $securityConfigMock;

    /** @var  \Magento\Security\Model\ResourceModel\PasswordResetRequestEvent\CollectionFactory */
    protected $model;

    /**
     * Init mocks for tests
     * @return void
     */
    protected function setUp()
    {
        $this->objectManagerMock = $this->getMockBuilder(\Magento\Framework\ObjectManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->securityConfigMock = $this->getMockBuilder(ConfigInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->model = (new ObjectManager($this))->getObject(
            CollectionFactory::class,
            [
                'objectManager' => $this->objectManagerMock,
                'securityConfig' => $this->securityConfigMock,
            ]
        );
    }

    /**
     * @param int $limitMethod
     * @param int $securityEventType
     * @param string $accountReference
     * @param string $longIp
     * @dataProvider createDataProvider
     */
    public function testCreate(
        $limitMethod,
        $securityEventType = null,
        $accountReference = null,
        $longIp = null
    ) {
        $collectionMcok = $this->getMockBuilder(Collection::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->objectManagerMock->expects($this->once())
            ->method('create')
            ->willReturn($collectionMcok);
        if ($securityEventType !== null) {
            $this->securityConfigMock->expects($this->once())
                ->method('getPasswordResetProtectionType')
                ->willReturn($limitMethod);
        }
        if ($limitMethod == ResetMethod::OPTION_BY_EMAIL) {
            $collectionMcok->expects($this->once())
                ->method('filterByAccountReference')
                ->with($accountReference);
        }
        if ($limitMethod == ResetMethod::OPTION_BY_IP) {
            $collectionMcok->expects($this->once())
                ->method('filterByIp')
                ->with($longIp);
        }
        if ($limitMethod == ResetMethod::OPTION_BY_IP_AND_EMAIL) {
            $collectionMcok->expects($this->once())
                ->method('filterByIpOrAccountReference')
                ->with($longIp, $accountReference);
        }
        $this->model->create($securityEventType, $accountReference, $longIp);
    }

    public function createDataProvider()
    {
        return [
            [null],
            [ResetMethod::OPTION_BY_EMAIL, 1, 'accountReference'],
            [ResetMethod::OPTION_BY_IP, 1, null, 'longIp'],
            [ResetMethod::OPTION_BY_IP_AND_EMAIL, 1, 'accountReference', 'longIp'],
        ];
    }
}
