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
namespace Magento\Framework\App\Helper;

class AbstractHelperTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\App\Helper\AbstractHelper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_helper = null;

    protected function setUp()
    {
        $context = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get('Magento\Framework\App\Helper\Context');
        $this->_helper = $this->getMock(
            'Magento\Framework\App\Helper\AbstractHelper',
            array('_getModuleName'),
            array($context)
        );
        $this->_helper->expects($this->any())->method('_getModuleName')->will($this->returnValue('Magento_Core'));
    }

    /**
     * @covers \Magento\Framework\App\Helper\AbstractHelper::isModuleEnabled
     * @covers \Magento\Framework\App\Helper\AbstractHelper::isModuleOutputEnabled
     */
    public function testIsModuleEnabled()
    {
        $this->assertTrue($this->_helper->isModuleEnabled());
        $this->assertTrue($this->_helper->isModuleOutputEnabled());
    }

    public function testUrlEncodeDecode()
    {
        $data = uniqid();
        $result = $this->_helper->urlEncode($data);
        $this->assertNotContains('&', $result);
        $this->assertNotContains('%', $result);
        $this->assertNotContains('+', $result);
        $this->assertNotContains('=', $result);
        $this->assertEquals($data, $this->_helper->urlDecode($result));
    }

    public function testTranslateArray()
    {
        $data = array(uniqid(), array(uniqid(), array(uniqid())));
        $this->assertEquals($data, $this->_helper->translateArray($data));
    }
}
