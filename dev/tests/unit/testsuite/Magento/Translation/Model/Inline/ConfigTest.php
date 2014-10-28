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
namespace Magento\Translation\Model\Inline;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Config
     */
    protected $model;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $scopeConfigMock;

    /**
     * @var \Magento\Core\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $helperMock;

    protected function setUp()
    {
        $this->scopeConfigMock = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');
        $this->helperMock = $this->getMock('Magento\Core\Helper\Data', array('isDevAllowed'), array(), '', false);
        $this->model = new Config(
            $this->scopeConfigMock,
            $this->helperMock
        );
    }

    public function testIsActive()
    {
        $store = 'some store';
        $result = 'result';
        $scopeConfig = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');
        $scopeConfig->expects(
            $this->once()
        )->method(
            'isSetFlag'
        )->with(
            $this->equalTo('dev/translate_inline/active'),
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->equalTo($store)
        )->will(
            $this->returnValue($result)
        );
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $config = $objectManager->getObject(
            '\Magento\Translation\Model\Inline\Config',
            array('scopeConfig' => $scopeConfig)
        );
        $this->assertEquals($result, $config->isActive($store));
    }

    public function testIsDevAllowed()
    {
        $store = 'some store';
        $result = 'result';

        $this->helperMock->expects(
            $this->once()
        )->method(
            'isDevAllowed'
        )->with(
            $store
        )->will(
            $this->returnValue($result)
        );

        $this->assertEquals($result, $this->model->isDevAllowed($store));
    }
}
