<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Tests for \Magento\Framework\Data\Form\Element\Date
 */
namespace Magento\Framework\Data\Test\Unit\Form\Element;

use \Magento\Framework\Data\Form\Element\Date;

class DateTest extends \PHPUnit\Framework\TestCase
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

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $localeDateMock;

    protected function setUp()
    {
        $this->factoryMock = $this->createMock(\Magento\Framework\Data\Form\Element\Factory::class);
        $this->collectionFactoryMock = $this->createMock(\Magento\Framework\Data\Form\Element\CollectionFactory::class);
        $this->escaperMock = $this->createMock(\Magento\Framework\Escaper::class);
        $this->localeDateMock = $this->createMock(\Magento\Framework\Stdlib\DateTime\TimezoneInterface::class);
        $this->model = new Date(
            $this->factoryMock,
            $this->collectionFactoryMock,
            $this->escaperMock,
            $this->localeDateMock
        );
    }

    public function testGetElementHtmlException()
    {
        $this->expectException('Exception');
        $this->expectExceptionMessage(
            'Output format is not specified. Please specify "format" key in constructor, or set it using setFormat().'
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

    /**
     * @return array
     */
    public function providerGetElementHtmlDateFormat()
    {
        return [
            ['date_format'],
            ['format'],
        ];
    }

    /**
     * @param $exactly
     * @return \PHPUnit_Framework_MockObject_MockObject
     */
    protected function getFormMock($exactly)
    {
        $functions = ['getFieldNameSuffix', 'getHtmlIdPrefix', 'getHtmlIdSuffix'];
        $formMock = $this->createPartialMock(\stdClass::class, $functions);
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
