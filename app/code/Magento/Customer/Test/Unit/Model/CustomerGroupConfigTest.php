<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class CustomerGroupConfigTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerHelper
     */
    private $objectManagerHelper;

    /**
     * @var \Magento\Customer\Model\CustomerGroupConfig
     */
    private $customerGroupConfig;

    /**
     * @var \Magento\Config\Model\Config|\PHPUnit\Framework\MockObject\MockObject
     */
    private $configMock;

    /**
     * @var \Magento\Customer\Api\GroupRepositoryInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    private $groupRepositoryMock;

    /**
     * Set up.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $this->configMock = $this->getMockBuilder(\Magento\Config\Model\Config::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->groupRepositoryMock = $this->getMockBuilder(\Magento\Customer\Api\GroupRepositoryInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->customerGroupConfig = $this->objectManagerHelper->getObject(
            \Magento\Customer\Model\CustomerGroupConfig::class,
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

        $customerGroupMock = $this->getMockBuilder(\Magento\Customer\Api\Data\GroupInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->groupRepositoryMock->expects($this->once())->method('getById')->willReturn($customerGroupMock);
        $this->configMock->expects($this->once())->method('setDataByPath')
            ->with(\Magento\Customer\Model\GroupManagement::XML_PATH_DEFAULT_ID, $customerGroupId)->willReturnSelf();
        $this->configMock->expects($this->once())->method('save');

        $this->assertEquals($customerGroupId, $this->customerGroupConfig->setDefaultCustomerGroup($customerGroupId));
    }
}
