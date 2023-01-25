<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\View\Test\Unit;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\EntitySpecificHandlesList;
use PHPUnit\Framework\TestCase;

class EntitySpecificHandlesListTest extends TestCase
{
    /**
     * @var EntitySpecificHandlesList
     */
    private $entitySpecificHandlesList;

    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
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
