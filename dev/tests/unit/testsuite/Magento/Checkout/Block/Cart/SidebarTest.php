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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Checkout\Block\Cart;

class SidebarTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\TestFramework\Helper\ObjectManager */
    protected $_objectManager;

    protected function setUp()
    {
        $this->_objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
    }

    public function testDeserializeRenders()
    {
        $childBlock = $this->getMock('Magento\Core\Block\AbstractBlock', array(), array(), '', false);
        /** @var $layout \Magento\Core\Model\Layout */
        $layout = $this->getMock('Magento\Core\Model\Layout', array(
            'createBlock', 'getChildName', 'setChild'
        ), array(), '', false);
        $layout->expects($this->once())
            ->method('createBlock')
            ->with(
                'some-block',
                '.some-template',
                array('data' => array('template' => 'some-type'))
            )
            ->will($this->returnValue($childBlock));
        $layout->expects($this->any())
            ->method('getChildName')
            ->with(null, 'some-template')
            ->will($this->returnValue(false));
        $layout->expects($this->once())
            ->method('setChild')
            ->with(null, null, 'some-template');

        /** @var $block \Magento\Checkout\Block\Cart\Sidebar */
        $block = $this->_objectManager->getObject('Magento\Checkout\Block\Cart\Sidebar', array(
            'context' => $this->_objectManager->getObject('Magento\Backend\Block\Template\Context', array(
                'layout' => $layout,
            ))
        ));

        $block->deserializeRenders('some-template|some-block|some-type');
    }
}
