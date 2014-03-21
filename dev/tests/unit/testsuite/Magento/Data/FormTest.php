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
namespace Magento\Data;

/**
 * Tests for \Magento\Data\FormFactory
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class FormTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_factoryElementMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_factoryCollectionMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_formKeyMock;

    /**
     * @var \Magento\Data\Form
     */
    protected $_form;

    protected function setUp()
    {
        $this->_factoryElementMock = $this->getMock(
            'Magento\Data\Form\Element\Factory',
            array('create'),
            array(),
            '',
            false
        );
        $this->_factoryCollectionMock = $this->getMock(
            'Magento\Data\Form\Element\CollectionFactory',
            array('create'),
            array(),
            '',
            false
        );
        $this->_factoryCollectionMock->expects($this->any())->method('create')->will($this->returnValue(array()));
        $this->_formKeyMock = $this->getMock('Magento\Data\Form\FormKey', array('getFormKey'), array(), '', false);

        $this->_form = new Form($this->_factoryElementMock, $this->_factoryCollectionMock, $this->_formKeyMock);
    }

    public function testFormKeyUsing()
    {
        $formKey = 'form-key';
        $this->_formKeyMock->expects($this->once())->method('getFormKey')->will($this->returnValue($formKey));

        $this->_form->setUseContainer(true);
        $this->_form->setMethod('post');
        $this->assertContains($formKey, $this->_form->toHtml());
    }
}
