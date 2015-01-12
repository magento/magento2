<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Tests for \Magento\Framework\Data\Form\Element\Date
 */
namespace Magento\Framework\Data\Form\Element;

class DateTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Data\Form\Element\Date
     */
    protected $model;

    /**
     * @var \Magento\Framework\Data\Form\Element\Factory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $factoryMock;

    /**
     * @var \Magento\Framework\Data\Form\Element\CollectionFactory|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $collectionFactoryMock;

    /**
     * @var \Magento\Framework\Escaper|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $escaperMock;

    protected function setUp()
    {
        $this->factoryMock = $this->getMock('Magento\Framework\Data\Form\Element\Factory', [], [], '', false);
        $this->collectionFactoryMock = $this->getMock(
            'Magento\Framework\Data\Form\Element\CollectionFactory',
            [],
            [],
            '',
            false
        );
        $this->escaperMock = $this->getMock('Magento\Framework\Escaper', [], [], '', false);
        $this->model = new Date(
            $this->factoryMock,
            $this->collectionFactoryMock,
            $this->escaperMock
        );
    }

    public function testGetElementHtmlException()
    {
        $this->setExpectedException(
            'Exception',
            'Output format is not specified. Please, specify "format" key in constructor, or set it using setFormat().'
        );
        $formMock = $this->getFormMock('never');
        $this->model->setForm($formMock);
        $this->model->getElementHtml();
    }

    /**
     * @param $fieldName
     * @dataProvider providerGetElementHtmlDateFormat
     */
    public function testGetElementHtmlDateFormat($fieldName)
    {
        $formMock = $this->getFormMock('once');
        $this->model->setForm($formMock);

        $this->model->setData([
                $fieldName => 'yyyy-MM-dd',
                'name' => 'test_name',
                'html_id' => 'test_name',
            ]);
        $this->model->getElementHtml();
    }

    public function providerGetElementHtmlDateFormat()
    {
        return [
            ['date_format'],
            ['format'],
        ];
    }

    protected function getFormMock($exactly)
    {
        $functions = ['getFieldNameSuffix', 'getHtmlIdPrefix', 'getHtmlIdSuffix'];
        $formMock = $this->getMock('stdClass', $functions);
        foreach ($functions as $method) {
            switch ($exactly) {
                case 'once':
                    $count = $this->once();
                    break;
                case 'never':
                default:
                    $count = $this->never();
            }
            $formMock->expects($count)->method($method);
        }

        return $formMock;
    }
}
