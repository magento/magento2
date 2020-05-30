<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model;

use Magento\Config\Model\Config;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Model\CustomerGroupConfig;
use Magento\Customer\Model\GroupManagement;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CustomerGroupConfigTest extends TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var CustomerGroupConfig
     */
    private $customerGroupConfig;

    /**
     * @var Config|MockObject
     */
    private $configMock;

    /**
     * @var GroupRepositoryInterface|MockObject
     */
    private $groupRepositoryMock;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->configMock = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->groupRepositoryMock = $this->getMockBuilder(GroupRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->customerGroupConfig = $this->objectManagerHelper->getObject(
            CustomerGroupConfig::class,
            [
                'config' => $this->configMock,
                'groupRepository' => $this->groupRepositoryMock
            ]
        );
    }

    /**
     * @return void
     */
    public function testSetDefaultCustomerGroup()
    {
        $customerGroupId = 1;

        $customerGroupMock = $this->getMockBuilder(GroupInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();
        $this->groupRepositoryMock->expects($this->once())->method('getById')->willReturn($customerGroupMock);
        $this->configMock->expects($this->once())->method('setDataByPath')
            ->with(GroupManagement::XML_PATH_DEFAULT_ID, $customerGroupId)->willReturnSelf();
        $this->configMock->expects($this->once())->method('save');

        $this->assertEquals($customerGroupId, $this->customerGroupConfig->setDefaultCustomerGroup($customerGroupId));
    }
}
