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
namespace Magento\Core\Model\Config\Scope\Processor;

class PlaceholderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Core\Model\Config\Scope\Processor\Placeholder
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_requestMock;

    protected function setUp()
    {
        $this->_requestMock = $this->getMock('Magento\App\Request\Http', array(), array(), '', false);
        $this->_requestMock->expects($this->any())
            ->method('getDistroBaseUrl')
            ->will($this->returnValue('http://localhost/'));
        $this->_model = new \Magento\Core\Model\Config\Scope\Processor\Placeholder($this->_requestMock);
    }

    public function testProcess()
    {
        $data = array(
            'web' => array(
                'unsecure' => array(
                    'base_url' => 'http://localhost/',
                    'base_link_url' => '{{unsecure_base_url}}website/de',
                ),
                'secure' => array(
                    'base_url' => 'https://localhost/',
                    'base_link_url' => '{{secure_base_url}}website/de',
                ),
            ),
            'path' => 'value',
            'some_url' => '{{base_url}}some'
        );
        $expectedResult = $data;
        $expectedResult['web']['unsecure']['base_link_url'] = 'http://localhost/website/de';
        $expectedResult['web']['secure']['base_link_url'] = 'https://localhost/website/de';
        $expectedResult['some_url'] = 'http://localhost/some';
        $this->assertEquals($expectedResult, $this->_model->process($data));
    }
}
