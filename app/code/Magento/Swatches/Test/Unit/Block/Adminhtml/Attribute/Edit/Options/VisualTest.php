<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Swatches\Test\Unit\Block\Adminhtml\Attribute\Edit\Options;

use Magento\Framework\DataObject;
use Magento\Framework\TestFramework\Unit\Helper\MockCreationTrait;
use Magento\Swatches\Block\Adminhtml\Attribute\Edit\Options\Visual;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class VisualTest extends TestCase
{
    use MockCreationTrait;
    /**
     * @var MockObject|Visual
     */
    private $model;

    /**
     * Setup environment for test
     */
    protected function setUp(): void
    {
        $this->model = $this->createPartialMockWithReflection(
            Visual::class,
            ['canManageOptionDefaultOnly', 'getOptionValues', 'getUrl', 'isReadOnly', 'getReadOnly']
        );
        $this->model->method('canManageOptionDefaultOnly')->willReturn(false);
        $this->model->method('getOptionValues')->willReturn([]);
        $this->model->method('getUrl')->willReturn('test-url');
        $this->model->method('isReadOnly')->willReturn(false);
        $this->model->method('getReadOnly')->willReturn(false);
        $this->model->read_only = false;
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
                'upload_action_url' => 'http://magento.com/admin/swatches/iframe/show',
                'option_values' => [
                    new DataObject(['value' => 6, 'label' => 'red']),
                    new DataObject(['value' => 6, 'label' => 'blue']),
                ]
            ],
            'expectedResult' => '{"attributesData":[{"value":6,"label":"red"},{"value":6,"label":"blue"}],' .
                '"uploadActionUrl":"http:\/\/magento.com\/admin\/swatches\/iframe\/show","isSortable":0,"isReadOnly":1}'

        ];

        $this->executeTest($testCase1);
    }

    /**
     * Test getJsonConfig with getReadOnly() is false and canManageOptionDefaultOnly() is false
     */
    public function testGetJsonConfigDataSet2()
    {
        $testCase1 = [
            'dataSet' => [
                'read_only' => false,
                'can_manage_option_default_only' => false,
                'upload_action_url' => 'http://magento.com/admin/swatches/iframe/show',
                'option_values' => [
                    new DataObject(['value' => 6, 'label' => 'red']),
                    new DataObject(['value' => 6, 'label' => 'blue']),
                ]
            ],
            'expectedResult' => '{"attributesData":[{"value":6,"label":"red"},{"value":6,"label":"blue"}],' .
                '"uploadActionUrl":"http:\/\/magento.com\/admin\/swatches\/iframe\/show","isSortable":1,"isReadOnly":0}'
        ];

        $this->executeTest($testCase1);
    }

    /**
     * Execute test for getJsonConfig() function
     */
    public function executeTest($testCase)
    {
        // Override methods for this test
        $dataSet = $testCase['dataSet'];
        $this->model = $this->createPartialMockWithReflection(
            Visual::class,
            ['canManageOptionDefaultOnly', 'getOptionValues', 'getUrl', 'isReadOnly', 'getReadOnly']
        );
        $this->model->method('canManageOptionDefaultOnly')
            ->willReturn($dataSet['can_manage_option_default_only'] ?? false);
        $this->model->method('getOptionValues')->willReturn($dataSet['option_values'] ?? []);
        $this->model->method('getUrl')->willReturn('http://magento.com/admin/swatches/iframe/show');
        $this->model->method('isReadOnly')->willReturn($dataSet['read_only'] ?? false);
        $this->model->method('getReadOnly')->willReturn($dataSet['read_only'] ?? false);
        $this->model->read_only = $dataSet['read_only'] ?? false;

        $this->assertEquals($testCase['expectedResult'], $this->model->getJsonConfig());
    }
}
