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
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Core\Model\Url;

class SecurityInfoTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_scopeConfigMock;

    /**
     * @var \Magento\Core\Model\Url\SecurityInfo
     */
    protected $_model;

    protected function setUp()
    {
        $this->_scopeConfigMock = $this->getMock('\Magento\Framework\App\Config\ScopeConfigInterface');
        $this->_model = new \Magento\Core\Model\Url\SecurityInfo($this->_scopeConfigMock, array('/account', '/cart'));
    }

    public function testIsSecureReturnsFalseIfDisabledInConfig()
    {
        $this->_scopeConfigMock->expects($this->once())->method('getValue')->will($this->returnValue(false));
        $this->assertFalse($this->_model->isSecure('http://example.com/account'));
    }

    /**
     * @param string $url
     * @param bool $expected
     * @dataProvider secureUrlDataProvider
     */
    public function testIsSecureChecksIfUrlIsInSecureList($url, $expected)
    {
        $this->_scopeConfigMock->expects($this->once())->method('getValue')->will($this->returnValue(true));
        $this->assertEquals($expected, $this->_model->isSecure($url));
    }

    public function secureUrlDataProvider()
    {
        return array(
            array('/account', true),
            array('/product', false),
            array('/product/12312', false),
            array('/cart', true),
            array('/cart/add', true)
        );
    }
}
