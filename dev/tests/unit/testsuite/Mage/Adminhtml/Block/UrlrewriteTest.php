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
 * @category    Magento
 * @package     Mage_Adminhtml
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Adminhtml_Block_UrlrewriteTest extends PHPUnit_Framework_TestCase
{
    /**
     * @param array $modes
     * @param string $expectedUrl
     * @dataProvider getCreateUrlData
     */
    public function testGetCreateUrl(array $modes, $expectedUrl)
    {
        /** @var $selectorBlock Mage_Adminhtml_Block_Urlrewrite_Selector */
        $selectorBlock = $modes
            ? $this->getMock('Mage_Adminhtml_Block_Urlrewrite_Selector', array('getModes'), array(), '', false)
            : false;
        if ($selectorBlock) {
            $selectorBlock->expects($this->once())->method('getModes')->with()->will($this->returnValue($modes));
        }

        $testedBlock = $this->getMock('Mage_Adminhtml_Block_Urlrewrite', array('getUrl'), array(), '', false);
        $testedBlock->setSelectorBlock($selectorBlock);
        $testedBlock->expects($this->once())
            ->method('getUrl')
            ->with('*/*/edit')
            ->will($this->returnValue('http://localhost/admin/urlrewrite/edit/'));

        $this->assertEquals($expectedUrl, $testedBlock->getCreateUrl());
    }

    /**
     * @static
     * @return array
     */
    public static function getCreateUrlData()
    {
        return array(
            array(
                array(),
                'http://localhost/admin/urlrewrite/edit/',
            ),
            array(
                array('category' => 'For category', 'product' => 'For product', 'id' => 'Custom'),
                'http://localhost/admin/urlrewrite/edit/category',
            ),
            array(
                array('product' => 'For product', 'category' => 'For category', 'id' => 'Custom'),
                'http://localhost/admin/urlrewrite/edit/product',
            ),
            array(
                array('id' => 'Custom', 'product' => 'For product', 'category' => 'For category'),
                'http://localhost/admin/urlrewrite/edit/id',
            ),
        );
    }
}
