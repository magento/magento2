<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleAdwords\Test\Unit\Model\Filter;

class UppercaseTitleTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\GoogleAdwords\Model\Filter\UppercaseTitle
     */
    protected $_model;

    protected function setUp()
    {
        $this->_model = new \Magento\GoogleAdwords\Model\Filter\UppercaseTitle();
    }

    /**
     * @return array
     */
    public function dataProviderForFilterValues()
    {
        return [['some name', 'Some Name'], ['test', 'Test']];
    }

    /**
     * @param string $inputValue
     * @param string $returnValue
     * @dataProvider dataProviderForFilterValues
     */
    public function testFilter($inputValue, $returnValue)
    {
        $this->assertEquals($returnValue, $this->_model->filter($inputValue));
    }
}
