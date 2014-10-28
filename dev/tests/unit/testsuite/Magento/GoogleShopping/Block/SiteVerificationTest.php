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
namespace Magento\GoogleShopping\Block;

class SiteVerificationTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\GoogleShopping\Block\SiteVerification */
    protected $_object;

    /** @var \Magento\GoogleShopping\Model\Config */
    protected $_config;

    protected function setUp()
    {
        $objectHelper = new \Magento\TestFramework\Helper\ObjectManager($this);

        $escaper = $this->getMockBuilder(
            'Magento\Framework\Escaper'
        )->disableOriginalConstructor()->setMethods(
            array('escapeHtml')
        )->getMock();
        $escaper->expects(
            $this->any()
        )->method(
            'escapeHtml'
        )->with(
            'Valor & Honor'
        )->will(
            $this->returnValue('Valor &amp; Honor')
        );

        $context = $objectHelper->getObject('Magento\Framework\View\Element\Context', array('escaper' => $escaper));

        $this->_config = $this->getMock('Magento\GoogleShopping\Model\Config', array(), array(), '', false);

        $this->_block = $objectHelper->getObject(
            'Magento\GoogleShopping\Block\SiteVerification',
            array('context' => $context, 'config' => $this->_config)
        );
    }

    public function testToHtmlWithContent()
    {
        $this->_config->expects(
            $this->once()
        )->method(
            'getConfigData'
        )->with(
            'verify_meta_tag'
        )->will(
            $this->returnValue('Valor & Honor')
        );
        $this->assertEquals(
            '<meta name="google-site-verification" content="Valor &amp; Honor"/>',
            $this->_block->toHtml()
        );
    }

    public function testToHtmlWithoutContent()
    {
        $this->_config->expects(
            $this->once()
        )->method(
            'getConfigData'
        )->with(
            'verify_meta_tag'
        )->will(
            $this->returnValue('')
        );
        $this->assertEquals('', $this->_block->toHtml());
    }
}
