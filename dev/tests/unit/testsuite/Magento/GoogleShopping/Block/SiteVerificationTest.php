<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
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
            ['escapeHtml']
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

        $context = $objectHelper->getObject('Magento\Framework\View\Element\Context', ['escaper' => $escaper]);

        $this->_config = $this->getMock('Magento\GoogleShopping\Model\Config', [], [], '', false);

        $this->_block = $objectHelper->getObject(
            'Magento\GoogleShopping\Block\SiteVerification',
            ['context' => $context, 'config' => $this->_config]
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
