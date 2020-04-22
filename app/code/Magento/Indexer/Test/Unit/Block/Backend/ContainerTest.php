<?php declare(strict_types=1);
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Test\Unit\Block\Backend;

use Magento\Backend\Block\Widget\Button\ButtonList;
use Magento\Backend\Block\Widget\Context;
use Magento\Framework\UrlInterface;
use Magento\Indexer\Block\Backend\Container;
use PHPUnit\Framework\TestCase;

class ContainerTest extends TestCase
{
    public function testPseudoConstruct()
    {
        $headerText = __('Indexer Management');
        $buttonList = $this->createPartialMock(
            ButtonList::class,
            ['remove', 'add']
        );
        $buttonList->expects($this->once())->method('add');
        $buttonList->expects($this->once())->method('remove')->with('add');
        $urlBuilderMock = $this->createMock(UrlInterface::class);
        $contextMock = $this->createPartialMock(
            Context::class,
            ['getUrlBuilder', 'getButtonList']
        );

        $contextMock->expects($this->once())->method('getUrlBuilder')->will($this->returnValue($urlBuilderMock));
        $contextMock->expects($this->once())->method('getButtonList')->will($this->returnValue($buttonList));

        $block = new Container($contextMock);

        $this->assertEquals($block->getHeaderText(), $headerText);
    }
}
