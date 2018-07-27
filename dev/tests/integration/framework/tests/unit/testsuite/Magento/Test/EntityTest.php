<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test;

class EntityTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Model\AbstractModel|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = $this->getMock(
            'Magento\Framework\Model\AbstractModel',
            ['load', 'save', 'delete', 'getIdFieldName', '__wakeup'],
            [],
            '',
            false
        );
    }

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
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function saveModelAndFailOnUpdate()
    {
        if (!$this->_model->getId()) {
            $this->saveModelSuccessfully();
        } else {
            throw new \Magento\Framework\Exception\LocalizedException(__('Synthetic model update failure.'));
        }
    }

    /**
     * Callback for delete method in mocked model
     */
    public function deleteModelSuccessfully()
    {
        $this->_model->setId(null);
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Class 'stdClass' is irrelevant to the tested model
     */
    public function testConstructorIrrelevantModelClass()
    {
        new \Magento\TestFramework\Entity($this->_model, [], 'stdClass');
    }

    public function crudDataProvider()
    {
        return [
            'successful CRUD' => ['saveModelSuccessfully'],
            'cleanup on update error' => ['saveModelAndFailOnUpdate', 'Magento\Framework\Exception\LocalizedException']
        ];
    }

    /**
     * @dataProvider crudDataProvider
     */
    public function testTestCrud($saveCallback, $expectedException = null)
    {
        $this->setExpectedException($expectedException);

        $this->_model->expects($this->atLeastOnce())
            ->method('load');
        $this->_model->expects($this->atLeastOnce())
            ->method('save')
            ->will($this->returnCallback([$this, $saveCallback]));
        /* It's important that 'delete' should be always called to guarantee the cleanup */
        $this->_model->expects(
            $this->atLeastOnce()
        )->method(
            'delete'
        )->will(
            $this->returnCallback([$this, 'deleteModelSuccessfully'])
        );

        $this->_model->expects($this->any())->method('getIdFieldName')->will($this->returnValue('id'));

        $test = $this->getMock(
            'Magento\TestFramework\Entity',
            ['_getEmptyModel'],
            [$this->_model, ['test' => 'test']]
        );

        $test->expects($this->any())->method('_getEmptyModel')->will($this->returnValue($this->_model));
        $test->testCrud();
    }
}
