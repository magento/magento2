<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Setup\Test\Unit\Model;

use \Magento\Setup\Model\PayloadValidator;

class PayloadValidatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Module\FullModuleList|\PHPUnit_Framework_MockObject_MockObject
     */
    private $fullModuleList;

    /**
     * @var  \Magento\Setup\Model\PayloadValidator
     */
    private $model;

    public function setUp()
    {
        $this->fullModuleList = $this->getMock('Magento\Framework\Module\FullModuleList', [], [], '', false);
        $this->model = new PayloadValidator($this->fullModuleList);
    }

    /**
     * @param string $type
     * @param int $has
     * @param bool $moduleExists
     * @dataProvider validatePayLoadDataProvider
     */
    public function testValidatePayLoad($type, $has, $moduleExists)
    {
        $this->fullModuleList->expects($this->exactly($has))->method('has')->willReturn($moduleExists);
        $this->assertEquals('', $this->model->validatePayload($type));
    }

    public function validatePayLoadDataProvider()
    {
        return [
            [['type' => 'uninstall', 'dataOption' => true], 0, false],
            [['type' => 'update', 'packages' => [['name' => 'vendor\/package', 'version' => '1.0.1']]], 0, false],
            [['type' => 'enable', 'packages' => [['name' => 'vendor\/package', 'version' => '1.0.1']]], 1, true],
            [['type' => 'disable', 'packages' => [['name' => 'vendor\/package', 'version' => '1.0.1']]], 1, true],
        ];
    }

    /**
     * @param string $type
     * @param int $has
     * @param bool $moduleExists
     * @param string $errorMessage
     * @dataProvider validatePayLoadNegativeCasesDataProvider
     */
    public function testValidatePayLoadNegativeCases($type, $has, $moduleExists, $errorMessage)
    {
        $this->fullModuleList->expects($this->exactly($has))->method('has')->willReturn($moduleExists);
        $this->assertStringStartsWith($errorMessage, $this->model->validatePayload($type));
    }

    public function validatePayLoadNegativeCasesDataProvider()
    {
        return [
            [['type' => 'uninstall'], 0, false, 'Missing dataOption'],
            [['type' => 'update'], 0, false, 'Missing packages'],
            [['type' => 'update',
                'packages' => [['name' => 'vendor\/package']]],
                0,
                false,
                'Missing package information'
            ],
            [['type' => 'enable'], 0, false, 'Missing packages'],
            [['type' => 'enable',
                'packages' => [['name' => 'vendor\/package']]],
                1,
                false,
                'Invalid Magento module name'
            ],
            [['type' => 'disable',
                'packages' => [['name' => 'vendor\/package']]],
                1,
                false,
                'Invalid Magento module name'
            ]
        ];
    }
}
