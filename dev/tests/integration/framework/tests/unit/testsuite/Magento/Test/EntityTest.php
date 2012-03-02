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
 * @package     Magento
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Magento_Test_EntityTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Mage_Core_Model_Abstract|PHPUnit_Framework_MockObject_MockObject
     */
    protected $_model;

    /**
     * Callback for save method in mocked model
     */
    public function saveModelSuccessfully()
    {
        $this->_model->setId('1');
    }

    /**
     * Callback for save method in mocked model
     *
     * @throws Magento_Exception
     */
    public function saveModelAndFailOnUpdate()
    {
        if (!$this->_model->getId()) {
            $this->saveModelSuccessfully();
        } else {
            throw new Magento_Exception('Synthetic model update failure.');
        }
    }

    /**
     * Callback for delete method in mocked model
     */
    public function deleteModelSuccessfully()
    {
        $this->_model->setId(null);
    }

    public function crudDataProvider()
    {
        return array(
            'successful CRUD'         => array('saveModelSuccessfully'),
            'cleanup on update error' => array('saveModelAndFailOnUpdate', 'Magento_Exception'),
        );
    }

    /**
     * @dataProvider crudDataProvider
     */
    public function testTestCrud($saveCallback, $expectedException = null)
    {
        $this->setExpectedException($expectedException);

        $this->_model = $this->getMock(
            'Mage_Core_Model_Abstract',
            array('load', 'save', 'delete', 'getIdFieldName')
        );

        $this->_model->expects($this->atLeastOnce())
            ->method('load');
        $this->_model->expects($this->atLeastOnce())
            ->method('save')
            ->will($this->returnCallback(array($this, $saveCallback)));
        /* It's important that 'delete' should be always called to guarantee the cleanup */
        $this->_model->expects($this->atLeastOnce())
            ->method('delete')
            ->will($this->returnCallback(array($this, 'deleteModelSuccessfully')));

        $this->_model->expects($this->any())
            ->method('getIdFieldName')
            ->will($this->returnValue('id'));

        $test = $this->getMock(
            'Magento_Test_Entity',
            array('_getEmptyModel'),
            array($this->_model, array('test' => 'test'))
        );

        $test->expects($this->any())
            ->method('_getEmptyModel')
            ->will($this->returnValue($this->_model));
        $test->testCrud();

    }
}
