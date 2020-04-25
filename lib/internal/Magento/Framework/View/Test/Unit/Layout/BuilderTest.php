<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Framework\View\Test\Unit\Layout;

use PHPUnit\Framework\TestCase;
use Magento\Framework\View\Layout\Builder;
use PHPUnit\Framework\MockObject\MockObject;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\View\Layout;
use Magento\Framework\View\Layout\ProcessorInterface;

/**
 * @covers \Magento\Framework\View\Layout\Builder
 */
class BuilderTest extends TestCase
{
    const CLASS_NAME = Builder::class;

    /**
     * @covers \Magento\Framework\View\Layout\Builder::build()
     */
    public function testBuild()
    {
        $fullActionName = 'route_controller_action';

        /** @var Http|MockObject */
        $request = $this->createMock(Http::class);
        $request->expects($this->exactly(3))->method('getFullActionName')->will($this->returnValue($fullActionName));

        /** @var ProcessorInterface|MockObject $processor */
        $processor = $this->createMock(ProcessorInterface::class);
        $processor->expects($this->once())->method('load');

        /** @var Layout|MockObject */
        $layout = $this->createPartialMock(
            Layout::class,
            $this->getLayoutMockMethods()
        );
        $layout->expects($this->atLeastOnce())->method('getUpdate')->will($this->returnValue($processor));
        $layout->expects($this->atLeastOnce())->method('generateXml')->will($this->returnValue($processor));
        $layout->expects($this->atLeastOnce())->method('generateElements')->will($this->returnValue($processor));

        $data = ['full_action_name' => $fullActionName, 'layout' => $layout];
        /** @var ManagerInterface|MockObject $eventManager */
        $eventManager = $this->createMock(ManagerInterface::class);
        $eventManager->expects($this->at(0))->method('dispatch')->with('layout_load_before', $data);
        $eventManager->expects($this->at(1))->method('dispatch')->with('layout_generate_blocks_before', $data);
        $eventManager->expects($this->at(2))->method('dispatch')->with('layout_generate_blocks_after', $data);
        $builder = $this->getBuilder(['eventManager' => $eventManager, 'request' => $request, 'layout' => $layout]);
        $builder->build();
    }

    /**
     * @return array
     */
    protected function getLayoutMockMethods()
    {
        return ['setBuilder', 'getUpdate', 'generateXml', 'generateElements'];
    }

    /**
     * @param array $arguments
     * @return \Magento\Framework\View\Layout\Builder
     */
    protected function getBuilder($arguments)
    {
        return (new ObjectManager($this))->getObject(static::CLASS_NAME, $arguments);
    }
}
