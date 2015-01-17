<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\DesignEditor\Block\Adminhtml\Theme\Selector\SelectorList;

class AbstractTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider getSampleCollection
     */
    public function testGetListItems($collection)
    {
        /** @var $listAbstractBlock
         *      \Magento\DesignEditor\Block\Adminhtml\Theme\Selector\SelectorList\AbstractSelectorList */
        $listAbstractBlock = $this->getMockForAbstractClass(
            'Magento\DesignEditor\Block\Adminhtml\Theme\Selector\SelectorList\AbstractSelectorList',
            [],
            '',
            false,
            false,
            true,
            ['getChildBlock', 'getLayout']
        );

        $themeMock = $this->getMock('Magento\DesignEditor\Block\Adminhtml\Theme', [], [], '', false);

        $listAbstractBlock->setCollection($collection);

        $listAbstractBlock->expects($this->atLeastOnce())
            ->method('getChildBlock')
            ->will($this->returnValue($themeMock));

        $this->assertEquals(2, count($listAbstractBlock->getListItems()));
    }

    /**
     * @return array
     */
    public function getSampleCollection()
    {
        return [[[['first_item'], ['second_item']]]];
    }

    public function testAddAssignButtonHtml()
    {
        /** @var $listAbstractBlock
         *      \Magento\DesignEditor\Block\Adminhtml\Theme\Selector\SelectorList\AbstractSelectorList */
        $listAbstractBlock = $this->getMockForAbstractClass(
            'Magento\DesignEditor\Block\Adminhtml\Theme\Selector\SelectorList\AbstractSelectorList',
            [],
            '',
            false,
            false,
            true,
            ['getChildBlock', 'getLayout']
        );
        /** @var $themeMock \Magento\Core\Model\Theme */
        $themeMock = $this->getMock('Magento\Core\Model\Theme', [], [], '', false);
        /** @var $themeBlockMock \Magento\DesignEditor\Block\Adminhtml\Theme */
        $themeBlockMock = $this->getMock(
            'Magento\DesignEditor\Block\Adminhtml\Theme',
            ['getTheme'],
            [],
            '',
            false
        );
        /** @var $layoutMock \Magento\Framework\View\LayoutInterface */
        $layoutMock = $this->getMock('Magento\Framework\View\Layout', ['createBlock'], [], '', false);
        /** @var $buttonMock \Magento\Backend\Block\Widget\Button */
        $buttonMock = $this->getMock('Magento\Backend\Block\Widget\Button', [], [], '', false);

        $layoutMock->expects($this->once())->method('createBlock')->will($this->returnValue($buttonMock));

        $themeBlockMock->expects($this->once())->method('getTheme')->will($this->returnValue($themeMock));

        $listAbstractBlock->expects($this->once())->method('getLayout')->will($this->returnValue($layoutMock));

        $themeMock->expects($this->once())->method('getId')->will($this->returnValue(1));

        $method = new \ReflectionMethod($listAbstractBlock, '_addAssignButtonHtml');
        $method->setAccessible(true);
        $method->invoke($listAbstractBlock, $themeBlockMock);
    }
}
