<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
            array(),
            '',
            false,
            false,
            true,
            array('getChildBlock')
        );

        $themeMock = $this->getMock('Magento\DesignEditor\Block\Adminhtml\Theme', array(), array(), '', false);

        $listAbstractBlock->setCollection($collection);

        $listAbstractBlock->expects(
            $this->atLeastOnce()
        )->method(
            'getChildBlock'
        )->will(
            $this->returnValue($themeMock)
        );

        $this->assertEquals(2, count($listAbstractBlock->getListItems()));
    }

    /**
     * @return array
     */
    public function getSampleCollection()
    {
        return array(array(array(array('first_item'), array('second_item'))));
    }

    public function testAddAssignButtonHtml()
    {
        /** @var $listAbstractBlock
         *      \Magento\DesignEditor\Block\Adminhtml\Theme\Selector\SelectorList\AbstractSelectorList */
        $listAbstractBlock = $this->getMockForAbstractClass(
            'Magento\DesignEditor\Block\Adminhtml\Theme\Selector\SelectorList\AbstractSelectorList',
            array(),
            '',
            false,
            false,
            true,
            array('getChildBlock', 'getLayout')
        );
        /** @var $themeMock \Magento\Core\Model\Theme */
        $themeMock = $this->getMock('Magento\Core\Model\Theme', array(), array(), '', false);
        /** @var $themeBlockMock \Magento\DesignEditor\Block\Adminhtml\Theme */
        $themeBlockMock = $this->getMock(
            'Magento\DesignEditor\Block\Adminhtml\Theme',
            array('getTheme'),
            array(),
            '',
            false
        );
        /** @var $layoutMock \Magento\Framework\View\LayoutInterface */
        $layoutMock = $this->getMock('Magento\Framework\View\Layout', array('createBlock'), array(), '', false);
        /** @var $buttonMock \Magento\Backend\Block\Widget\Button */
        $buttonMock = $this->getMock('Magento\Backend\Block\Widget\Button', array(), array(), '', false);

        $layoutMock->expects($this->once())->method('createBlock')->will($this->returnValue($buttonMock));

        $themeBlockMock->expects($this->once())->method('getTheme')->will($this->returnValue($themeMock));

        $listAbstractBlock->expects($this->once())->method('getLayout')->will($this->returnValue($layoutMock));

        $themeMock->expects($this->once())->method('getId')->will($this->returnValue(1));

        $method = new \ReflectionMethod($listAbstractBlock, '_addAssignButtonHtml');
        $method->setAccessible(true);
        $method->invoke($listAbstractBlock, $themeBlockMock);
    }
}
