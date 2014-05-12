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
namespace Magento\Backend\Block;

class UrlrewriteTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @param array $modes
     * @param string $expectedUrl
     * @dataProvider getCreateUrlData
     */
    public function testGetCreateUrl(array $modes, $expectedUrl)
    {
        /** @var $selectorBlock \Magento\Backend\Block\Urlrewrite\Selector */
        $selectorBlock = $modes ? $this->getMock(
            'Magento\Backend\Block\Urlrewrite\Selector',
            array('getModes'),
            array(),
            '',
            false
        ) : false;
        if ($selectorBlock) {
            $selectorBlock->expects($this->once())->method('getModes')->with()->will($this->returnValue($modes));
        }

        $testedBlock = $this->getMock('Magento\Backend\Block\Urlrewrite', array('getUrl'), array(), '', false);
        $testedBlock->setSelectorBlock($selectorBlock);
        $testedBlock->expects(
            $this->once()
        )->method(
            'getUrl'
        )->with(
            'adminhtml/*/edit'
        )->will(
            $this->returnValue('http://localhost/admin/urlrewrite/edit/')
        );

        $this->assertEquals($expectedUrl, $testedBlock->getCreateUrl());
    }

    /**
     * Data for testGetCreateUrl
     * @static
     * @return array
     */
    public static function getCreateUrlData()
    {
        return array(
            array(array(), 'http://localhost/admin/urlrewrite/edit/'),
            array(
                array(
                    'category' => 'For category',
                    'product' => 'For product',
                    'id' => 'Custom',
                    'cms_page' => 'For CMS page'
                ),
                'http://localhost/admin/urlrewrite/edit/category'
            ),
            array(
                array(
                    'product' => 'For product',
                    'category' => 'For category',
                    'id' => 'Custom',
                    'cms_page' => 'For CMS page'
                ),
                'http://localhost/admin/urlrewrite/edit/product'
            ),
            array(
                array(
                    'id' => 'Custom',
                    'product' => 'For product',
                    'category' => 'For category',
                    'cms_page' => 'For CMS page'
                ),
                'http://localhost/admin/urlrewrite/edit/id'
            ),
            array(
                array(
                    'cms_page' => 'For CMS page',
                    'product' => 'For product',
                    'category' => 'For category',
                    'id' => 'Custom'
                ),
                'http://localhost/admin/urlrewrite/edit/cms_page'
            )
        );
    }
}
