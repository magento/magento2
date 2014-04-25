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
namespace Magento\Checkout\Block;

class LinkTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\TestFramework\Helper\ObjectManager
     */
    protected $_objectManagerHelper;

    protected function setUp()
    {
        $this->_objectManagerHelper = new \Magento\TestFramework\Helper\ObjectManager($this);
    }

    public function testGetUrl()
    {
        $path = 'checkout';
        $url = 'http://example.com/';

        $urlBuilder = $this->getMockForAbstractClass('Magento\Framework\UrlInterface');
        $urlBuilder->expects($this->once())->method('getUrl')->with($path)->will($this->returnValue($url . $path));

        $context = $this->_objectManagerHelper->getObject(
            'Magento\Framework\View\Element\Template\Context',
            array('urlBuilder' => $urlBuilder)
        );
        $link = $this->_objectManagerHelper->getObject('Magento\Checkout\Block\Link', array('context' => $context));
        $this->assertEquals($url . $path, $link->getHref());
    }

    /**
     * @dataProvider toHtmlDataProvider
     */
    public function testToHtml($canOnepageCheckout, $isOutputEnabled)
    {
        $helper = $this->getMockBuilder(
            'Magento\Checkout\Helper\Data'
        )->disableOriginalConstructor()->setMethods(
            array('canOnepageCheckout', 'isModuleOutputEnabled')
        )->getMock();

        $moduleManager = $this->getMockBuilder(
            'Magento\Framework\Module\Manager'
        )->disableOriginalConstructor()->setMethods(
            array('isOutputEnabled')
        )->getMock();

        /** @var \Magento\Checkout\Block\Link $block */
        $block = $this->_objectManagerHelper->getObject(
            'Magento\Checkout\Block\Link',
            array('moduleManager' => $moduleManager, 'checkoutHelper' => $helper)
        );
        $helper->expects($this->any())->method('canOnepageCheckout')->will($this->returnValue($canOnepageCheckout));
        $moduleManager->expects(
            $this->any()
        )->method(
            'isOutputEnabled'
        )->with(
            'Magento_Checkout'
        )->will(
            $this->returnValue($isOutputEnabled)
        );
        $this->assertEquals('', $block->toHtml());
    }

    public function toHtmlDataProvider()
    {
        return array(array(false, true), array(true, false), array(false, false));
    }
}
