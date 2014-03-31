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
     * @var \Magento\Core\Model\Store\Config|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $storeConfigMock;

    /**
     * @var \Magento\Core\Helper\Data|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $helperMock;

    protected function setUp()
    {
        $this->storeConfigMock = $this->getMock(
            'Magento\Core\Model\Store\Config',
            array('getConfigFlag'),
            array(),
            '',
            false
        );
        $this->helperMock = $this->getMock('Magento\Core\Helper\Data', array('isDevAllowed'), array(), '', false);
        $this->model = new Config(
            $this->storeConfigMock,
            $this->helperMock
        );
    }

    public function testIsActive()
    {
        $store = 'some store';
        $result = 'result';

        $this->storeConfigMock->expects(
            $this->once()
        )->method(
            'getConfigFlag'
        )->with(
            $this->equalTo('dev/translate_inline/active'),
            $this->equalTo($store)
        )->will(
            $this->returnValue($result)
        );

        $this->assertEquals($result, $this->model->isActive($store));
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
