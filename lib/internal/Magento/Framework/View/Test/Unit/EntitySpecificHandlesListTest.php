<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Test\Unit;

use Magento\Framework\View\EntitySpecificHandlesList;

class EntitySpecificHandlesListTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EntitySpecificHandlesList
     */
    private $entitySpecificHandlesList;

    protected function setUp()
    {
        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->entitySpecificHandlesList = $objectManager->getObject(EntitySpecificHandlesList::class);
    }

    public function testAddAndGetHandles()
    {
        $this->assertEquals([], $this->entitySpecificHandlesList->getHandles());
        $this->entitySpecificHandlesList->addHandle('handle1');
        $this->entitySpecificHandlesList->addHandle('handle2');
        $this->assertEquals(['handle1', 'handle2'], $this->entitySpecificHandlesList->getHandles());
    }
}
