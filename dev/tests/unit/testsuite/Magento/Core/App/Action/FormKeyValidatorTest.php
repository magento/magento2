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
namespace Magento\Core\App\Action;

class FormKeyValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Core\App\Action\FormKeyValidator
     */
    protected $_model;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_formKeyMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_requestMock;

    protected function setUp()
    {
        $this->_formKeyMock = $this->getMock(
            '\Magento\Framework\Data\Form\FormKey',
            array('getFormKey'),
            array(),
            '',
            false
        );
        $this->_requestMock = $this->getMock('Magento\Framework\App\Request\Http', array(), array(), '', false);
        $this->_model = new \Magento\Core\App\Action\FormKeyValidator($this->_formKeyMock);
    }

    /**
     * @param string $formKey
     * @param bool $expected
     * @dataProvider validateDataProvider
     */
    public function testValidate($formKey, $expected)
    {
        $this->_requestMock->expects(
            $this->once()
        )->method(
            'getParam'
        )->with(
            'form_key',
            null
        )->will(
            $this->returnValue($formKey)
        );
        $this->_formKeyMock->expects($this->once())->method('getFormKey')->will($this->returnValue('formKey'));
        $this->assertEquals($expected, $this->_model->validate($this->_requestMock));
    }

    public function validateDataProvider()
    {
        return array(
            'formKeyExist' => array('formKey', true),
            'formKeyNotEqualToFormKeyInSession' => array('formKeySession', false)
        );
    }
}
