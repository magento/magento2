<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Swatches\Test\Unit\Block\Adminhtml\Attribute\Edit\Options;

use Magento\Framework\DataObject;
use Magento\Swatches\Block\Adminhtml\Attribute\Edit\Options\Text;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TextTest extends TestCase
{
    /**
     * @var MockObject|Text
     */
    private $model;

    /**
     * Setup environment for test
     */
    protected function setUp(): void
    {
        $this->model = $this->getMockBuilder(Text::class)
            ->disableOriginalConstructor()
            ->setMethods(['getReadOnly', 'canManageOptionDefaultOnly', 'getOptionValues'])
            ->getMock();
    }

    /**
     * Test getJsonConfig with getReadOnly() is true and canManageOptionDefaultOnly() is false
     */
    public function testGetJsonConfigDataSet1()
    {
        $testCase1 = [
            'dataSet' => [
                'read_only' => true,
                'can_manage_option_default_only' => false,
                'option_values' => [
                    new DataObject(['value' => 6, 'label' => 'red']),
                    new DataObject(['value' => 6, 'label' => 'blue']),
                ]
            ],
            'expectedResult' => '{"attributesData":[{"value":6,"label":"red"},{"value":6,"label":"blue"}],' .
                '"isSortable":0,"isReadOnly":1}'

        ];

        $this->executeTest($testCase1);
    }

    /**
     * Test getJsonConfig with getReadOnly() is false and canManageOptionDefaultOnly() is false
     */
    public function testGetJsonConfigDataSet2()
    {
        $testCase2 = [
            'dataSet' => [
                'read_only' => false,
                'can_manage_option_default_only' => false,
                'option_values' => [
                    new DataObject(['value' => 6, 'label' => 'red']),
                    new DataObject(['value' => 6, 'label' => 'blue']),
                ]
            ],
            'expectedResult' => '{"attributesData":[{"value":6,"label":"red"},{"value":6,"label":"blue"}],' .
                '"isSortable":1,"isReadOnly":0}'

        ];

        $this->executeTest($testCase2);
    }

    /**
     * Execute test for getJsonConfig() function
     */
    public function executeTest($testCase)
    {
        $this->model->expects($this->any())->method('getReadOnly')
            ->willReturn($testCase['dataSet']['read_only']);
        $this->model->expects($this->any())->method('canManageOptionDefaultOnly')
            ->willReturn($testCase['dataSet']['can_manage_option_default_only']);
        $this->model->expects($this->any())->method('getOptionValues')->willReturn(
            $testCase['dataSet']['option_values']
        );

        $this->assertEquals($testCase['expectedResult'], $this->model->getJsonConfig());
    }
}
