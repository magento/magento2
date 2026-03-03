<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\GoogleAdwords\Test\Unit\Model\Filter;

use Magento\GoogleAdwords\Model\Filter\UppercaseTitle;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class UppercaseTitleTest extends TestCase
{
    /**
     * @var UppercaseTitle
     */
    protected $_model;

    protected function setUp(): void
    {
        $this->_model = new UppercaseTitle();
    }

    /**
     * @return array
     */
    public static function dataProviderForFilterValues()
    {
        return [['some name', 'Some Name'], ['test', 'Test']];
    }

    /**
     * @param string $inputValue
     * @param string $returnValue
     */
    #[DataProvider('dataProviderForFilterValues')]
    public function testFilter($inputValue, $returnValue)
    {
        $this->assertEquals($returnValue, $this->_model->filter($inputValue));
    }
}
