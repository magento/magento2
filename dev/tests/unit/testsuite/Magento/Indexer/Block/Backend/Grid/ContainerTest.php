<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Block\Backend;

class ContainerTest extends \PHPUnit_Framework_TestCase
{
    public function testPseudoConstruct()
    {
        $controller = 'indexer';
        $blockGroup = 'Magento_Indexer';
        $contextMock = $this->getMockBuilder('\Magento\Backend\Block\Widget\Context')
            ->disableOriginalConstructor()
            ->getMock();
        $block = new Container($contextMock);

        $this->assertEquals($block->_controller, $controller);
        $this->assertEquals($block->_blockGroup, $blockGroup);
    }
}
