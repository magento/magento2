<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Console\Command;

use Magento\Customer\Console\Command\UpgradeHashAlgorithmCommand;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class UpgradeHashAlgorithmCommandTest extends TestCase
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
     * @var CollectionFactory|MockObject
     */
    private $customerCollectionFactory;

    protected function setUp(): void
    {
        $this->customerCollectionFactory = $this->getMockBuilder(
            CollectionFactory::class
        )->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->objectManager = new ObjectManager($this);

        $this->command = $this->objectManager->getObject(
            UpgradeHashAlgorithmCommand::class,
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
