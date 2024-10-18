<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Indexer\Test\Unit\Block\Backend;

use Magento\Backend\Block\Widget\Button\ButtonList;
use Magento\Backend\Block\Widget\Context;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\Indexer\Block\Backend\Container;
use PHPUnit\Framework\TestCase;

class ContainerTest extends TestCase
{
    public function testPseudoConstruct()
    {
        $objectManager = new ObjectManager($this);

        $objects = [
            [
                JsonHelper::class,
                $this->createMock(JsonHelper::class)
            ],
            [
                DirectoryHelper::class,
                $this->createMock(DirectoryHelper::class)
            ]
        ];
        $objectManager->prepareObjectManager($objects);
        $headerText = __('Indexer Management');
        $buttonList = $this->createPartialMock(
            ButtonList::class,
            ['remove', 'add']
        );
        $buttonList->expects($this->once())->method('add');
        $buttonList->expects($this->once())->method('remove')->with('add');
        $urlBuilderMock = $this->getMockForAbstractClass(UrlInterface::class);
        $contextMock = $this->createPartialMock(
            Context::class,
            ['getUrlBuilder', 'getButtonList']
        );

        $contextMock->expects($this->once())->method('getUrlBuilder')->willReturn($urlBuilderMock);
        $contextMock->expects($this->once())->method('getButtonList')->willReturn($buttonList);
        $objectManagerHelper = new ObjectManager($this);
        $objects = [
            [
                JsonHelper::class,
                $this->createMock(JsonHelper::class)
            ],
            [
                DirectoryHelper::class,
                $this->createMock(DirectoryHelper::class)
            ]
        ];
        $objectManagerHelper->prepareObjectManager($objects);
        $block = new Container($contextMock);

        $this->assertEquals($block->getHeaderText(), $headerText);
    }
}
