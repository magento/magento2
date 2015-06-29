<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\Composer\Test\Unit;

use Magento\Setup\Model\ComponentManager;
use Magento\Framework\Composer\ComponentReader;

class ComponentReaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ComponentReader
     */
    private $reader;

    public function __construct()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->reader = $objectManager->getObject(
            'Magento\Framework\Composer\ComponentReader',
            ['rootDir' => BP]
        );
    }

    public function testGetComponents()
    {
        $components = $this->reader->getComponents();
        $this->assertNotNull($components);
    }
}
