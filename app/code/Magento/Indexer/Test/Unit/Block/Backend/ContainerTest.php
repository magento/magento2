<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Test\Unit\Block\Backend;

class ContainerTest extends \PHPUnit_Framework_TestCase
{
    public function testPseudoConstruct()
    {
        $headerText = __('Indexer Management');
        $buttonList = $this->getMock('\Magento\Backend\Block\Widget\Button\ButtonList', ['remove', 'add'], [], '', false);
        $buttonList->expects($this->once())->method('add');
        $buttonList->expects($this->once())->method('remove')->with('add');
        $urlBuilderMock = $this->getMock('\Magento\Framework\UrlInterface', [], [], '', false);
        $contextMock = $this->getMock('\Magento\Backend\Block\Widget\Context', ['getUrlBuilder', 'getButtonList'], [], '', false);

        $contextMock->expects($this->once())->method('getUrlBuilder')->will($this->returnValue($urlBuilderMock));
        $contextMock->expects($this->once())->method('getButtonList')->will($this->returnValue($buttonList));

        $block = new \Magento\Indexer\Block\Backend\Container($contextMock);

        $this->assertEquals($block->getHeaderText(), $headerText);
    }
}
