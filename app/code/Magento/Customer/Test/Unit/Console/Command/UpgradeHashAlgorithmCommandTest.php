<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Console\Command;

use Magento\Customer\Console\Command\UpgradeHashAlgorithmCommand;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

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

    public function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        
        $this->command = $this->objectManager
            ->getObject('Magento\Customer\Console\Command\UpgradeHashAlgorithmCommand');
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
