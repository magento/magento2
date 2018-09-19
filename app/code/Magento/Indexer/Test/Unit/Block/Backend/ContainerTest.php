<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Test\Unit\Block\Backend;

class ContainerTest extends \PHPUnit\Framework\TestCase
{
    public function testPseudoConstruct()
    {
        $headerText = __('Indexer Management');
        $buttonList = $this->createPartialMock(
            \Magento\Backend\Block\Widget\Button\ButtonList::class,
            ['remove', 'add']
        );
        $buttonList->expects($this->once())->method('add');
        $buttonList->expects($this->once())->method('remove')->with('add');
        $urlBuilderMock = $this->createMock(\Magento\Framework\UrlInterface::class);
        $contextMock = $this->createPartialMock(
            \Magento\Backend\Block\Widget\Context::class,
            ['getUrlBuilder', 'getButtonList']
        );

        $contextMock->expects($this->once())->method('getUrlBuilder')->will($this->returnValue($urlBuilderMock));
        $contextMock->expects($this->once())->method('getButtonList')->will($this->returnValue($buttonList));

        $block = new \Magento\Indexer\Block\Backend\Container($contextMock);

        $this->assertEquals($block->getHeaderText(), $headerText);
    }
}
