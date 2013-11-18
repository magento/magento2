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
 * @package     Magento_Catalog
 * @subpackage  unit_tests
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Catalog\Helper;

class UrlTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Core\Model\Config */
    protected $_configMock;

    /** @var  \Magento\Catalog\Helper\Product\Url */
    protected $_urlHelper;

    protected function setUp()
    {
        $contextMock = $this->getMockBuilder('Magento\Core\Helper\Context')->disableOriginalConstructor()->getMock();
        $this->_configMock = $this->getMockBuilder('Magento\Core\Model\Config')
            ->disableOriginalConstructor()->getMock();
        $storeManager = $this->getMock('Magento\Core\Model\StoreManager', array(), array(), '', false);
        $this->_urlHelper = new \Magento\Catalog\Helper\Product\Url($contextMock, $this->_configMock, $storeManager);
    }

    /**
     * @param string $testString
     * @param string $result
     * @param string $resultIconv
     * @param bool $isIconv
     * @dataProvider validateStringFormat
     */
    public function testFormat($testString, $result, $resultIconv, $isIconv)
    {
        if ($isIconv) {
            $this->assertEquals($resultIconv, $this->_urlHelper->format($testString));
        } else {
            $this->assertEquals($result, $this->_urlHelper->format($testString));
        }
    }

    /**
     * @return array
     */
    public static function validateStringFormat()
    {
        $isIconv = '"libiconv"' == ICONV_IMPL;
        return array(
            array('test', 'test', 'test', $isIconv),
            array('привет мир', 'privet mir', 'privet mir', $isIconv),
            array(
                'Weiß, Goldmann, Göbel, Weiss, Göthe, Goethe und Götz',
                'Weiss, Goldmann, Gobel, Weiss, Gothe, Goethe und Gotz',
                'Weiss, Goldmann, Gobel, Weiss, Gothe, Goethe und Gotz',
                $isIconv
            ),
            array(
                '❤ ☀ ☆ ☂ ☻ ♞ ☯ ☭ ☢ € → ☎ ❄ ♫ ✂ ▷ ✇ ♎ ⇧ ☮',
                '❤ ☀ ☆ ☂ ☻ ♞ ☯ ☭ ☢ € → ☎ ❄ ♫ ✂ ▷ ✇ ♎ ⇧ ☮',
                '         EUR ->         ',
                $isIconv
            ),
        );
    }
}
