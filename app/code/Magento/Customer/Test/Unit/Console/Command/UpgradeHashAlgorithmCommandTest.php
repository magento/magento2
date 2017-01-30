<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Console\Command;

use Magento\Customer\Console\Command\UpgradeHashAlgorithmCommand;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;

class UpgradeHashAlgorithmCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UpgradeHashAlgorithmCommand
     */
    private $command;

    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $customerCollectionFactory;

    public function setUp()
    {
        $this->customerCollectionFactory = $this->getMockBuilder(
            'Magento\Customer\Model\ResourceModel\Customer\CollectionFactory'
        )->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManager = new ObjectManager($this);

        $this->command = $this->objectManager->getObject(
            'Magento\Customer\Console\Command\UpgradeHashAlgorithmCommand',
            [
                'customerCollectionFactory' => $this->customerCollectionFactory
            ]
        );
    }

    public function testConfigure()
    {
        $this->assertEquals('customer:hash:upgrade', $this->command->getName());
        $this->assertEquals(
            'Upgrade customer\'s hash according to the latest algorithm',
            $this->command->getDescription()
        );
    }
}
