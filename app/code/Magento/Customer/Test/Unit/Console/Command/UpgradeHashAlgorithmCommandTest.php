<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Console\Command;

use Magento\Customer\Console\Command\UpgradeHashAlgorithmCommand;
use Magento\Customer\Model\Resource\Customer\CollectionFactory;
use Magento\Framework\Encryption\Encryptor;

class UpgradeHashAlgorithmCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var UpgradeHashAlgorithmCommand
     */
    private $command;

    /**
     * @var CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    private $collectionFactory;

    /**
     * @var Encryptor|\PHPUnit_Framework_MockObject_MockObject
     */
    private $encryptor;

    public function setUp()
    {
        $this->collectionFactory = $this->getMockBuilder('Magento\Customer\Model\Resource\Customer\CollectionFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $this->encryptor = $this->getMockBuilder('Magento\Framework\Encryption\Encryptor')
            ->disableOriginalConstructor()
            ->getMock();

        $this->command = new UpgradeHashAlgorithmCommand($this->collectionFactory, $this->encryptor);
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
