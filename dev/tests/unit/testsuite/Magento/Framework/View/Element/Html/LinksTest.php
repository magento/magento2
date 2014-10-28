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
namespace Magento\Framework\View\Element\Html;

class LinksTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $_objectManagerHelper;

    /** @var \Magento\Framework\View\Element\Html\Links */
    protected $_block;

    /** @var \Magento\Framework\View\Element\Template\Context */
    protected $_context;

    protected function setUp()
    {
        $this->_objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);

        /** @var  \Magento\Framework\View\Element\Template\Context $context */
        $this->_context = $this->_objectManagerHelper->getObject('Magento\Framework\View\Element\Template\Context');

        /** @var \Magento\Framework\View\Element\Html\Links $block */
        $this->_block = $this->_objectManagerHelper->getObject(
            'Magento\Framework\View\Element\Html\Links',
            array('context' => $this->_context)
        );
    }

    public function testGetLinks()
    {
        $blocks = array(0 => 'blocks');
        $name = 'test_name';
        $this->_context->getLayout()->expects(
            $this->once()
        )->method(
            'getChildBlocks'
        )->with(
            $name
        )->will(
            $this->returnValue($blocks)
        );
        $this->_block->setNameInLayout($name);
        $this->assertEquals($blocks, $this->_block->getLinks());
    }

    public function testRenderLink()
    {
        $blockHtml = 'test';
        $name = 'test_name';
        $this->_context->getLayout()->expects(
            $this->once()
        )->method(
            'renderElement'
        )->with(
            $name
        )->will(
            $this->returnValue($blockHtml)
        );

        /** @var \Magento\Framework\View\Element\AbstractBlock $link */
        $link = $this->getMockBuilder('Magento\Framework\View\Element\AbstractBlock')
            ->disableOriginalConstructor()
            ->getMock();
        $link->expects($this->once())->method('getNameInLayout')->will($this->returnValue($name));

        $this->assertEquals($blockHtml, $this->_block->renderLink($link));
    }
}
