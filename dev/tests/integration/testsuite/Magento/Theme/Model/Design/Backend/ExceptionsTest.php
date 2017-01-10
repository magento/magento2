<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Theme\Model\Design\Backend;

class ExceptionsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Theme\Model\Design\Backend\Exceptions
     */
    protected $_model = null;

    protected function setUp()
    {
        $this->_model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            \Magento\Theme\Model\Design\Backend\Exceptions::class
        );
        $this->_model->setScope('default');
        $this->_model->setScopeId(0);
        $this->_model->setPath('design/theme/ua_regexp');
    }

    /**
     * Basic test, checks that saved value contains all required entries and is saved as an array
     * @magentoDbIsolation enabled
     */
    public function testSaveValueIsFormedNicely()
    {
        $value = [
            '1' => ['search' => '/Opera/', 'value' => 'Magento/blank'],
            '2' => ['search' => '/Firefox/', 'value' => 'Magento/blank'],
        ];

        $this->_model->setValue($value);
        $this->_model->save();

        $processedValue = unserialize($this->_model->getValue());
        $this->assertEquals(count($processedValue), 2, 'Number of saved values is wrong');

        $entry = $processedValue['1'];
        $this->assertArrayHasKey('search', $entry);
        $this->assertArrayHasKey('value', $entry);
        $this->assertArrayHasKey('regexp', $entry);
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testSaveEmptyValueIsSkipped()
    {
        $value = [
            '1' => ['search' => '/Opera/', 'value' => 'Magento/blank'],
            '2' => ['search' => '', 'value' => 'Magento/blank'],
            '3' => ['search' => '/Firefox/', 'value' => 'Magento/blank'],
        ];

        $this->_model->setValue($value);
        $this->_model->save();

        $processedValue = unserialize($this->_model->getValue());
        $emptyIsSkipped = isset($processedValue['1']) && !isset($processedValue['2']) && isset($processedValue['3']);
        $this->assertTrue($emptyIsSkipped);
    }

    /**
     * @param array $designException
     * @param string $regexp
     * @dataProvider saveExceptionDataProvider
     * @magentoDbIsolation enabled
     */
    public function testSaveException($designException, $regexp)
    {
        $this->_model->setValue(['1' => $designException]);
        $this->_model->save();

        $processedValue = unserialize($this->_model->getValue());
        $this->assertEquals($processedValue['1']['regexp'], $regexp);
    }

    /**
     * @return array
     */
    public function saveExceptionDataProvider()
    {
        $result = [
            [['search' => 'Opera', 'value' => 'Magento/blank'], '/Opera/i'],
            [['search' => '/Opera/', 'value' => 'Magento/blank'], '/Opera/'],
            [['search' => '#iPad|iPhone#i', 'value' => 'Magento/blank'], '#iPad|iPhone#i'],
            [
                ['search' => 'Mozilla (3.6+)/Firefox', 'value' => 'Magento/blank'],
                '/Mozilla \\(3\\.6\\+\\)\\/Firefox/i'
            ],
        ];

        return $result;
    }

    /**
     * @var array $value
     * @expectedException \Magento\Framework\Exception\LocalizedException
     * @dataProvider saveWrongExceptionDataProvider
     * @magentoDbIsolation enabled
     */
    public function testSaveWrongException($value)
    {
        $this->_model->setValue($value);
        $this->_model->save();
    }

    /**
     * @return array
     */
    public function saveWrongExceptionDataProvider()
    {
        $result = [
            [
                [
                    '1' => ['search' => '/Opera/', 'value' => 'Magento/blank'],
                    '2' => ['search' => '/invalid_regexp(/', 'value' => 'Magento/blank'],
                ],
            ],
            [
                [
                    '1' => ['search' => '/invalid_regexp', 'value' => 'Magento/blank'],
                    '2' => ['search' => '/Opera/', 'value' => 'Magento/blank'],
                ]
            ],
            [
                [
                    '1' => ['search' => 'invalid_regexp/iU', 'value' => 'Magento/blank'],
                    '2' => ['search' => '/Opera/', 'value' => 'Magento/blank'],
                ]
            ],
            [
                [
                    '1' => ['search' => 'invalid_regexp#', 'value' => 'Magento/blank'],
                    '2' => ['search' => '/Opera/', 'value' => 'Magento/blank'],
                ]
            ],
            [
                [
                    '1' => ['search' => '/Firefox/'],
                    '2' => ['search' => '/Opera/', 'value' => 'Magento/blank'],
                ]
            ],
            [
                [
                    '1' => ['value' => 'Magento/blank'],
                    '2' => ['search' => '/Opera/', 'value' => 'Magento/blank'],
                ]
            ],
        ];

        return $result;
    }
}
