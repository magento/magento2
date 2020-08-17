<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Config\Test\Unit\Model\Config\Backend\File;

use Magento\Config\Model\Config\Backend\File\RequestData;
use PHPUnit\Framework\TestCase;

class RequestDataTest extends TestCase
{
    /**
     * @var RequestData
     */
    protected $_model;

    protected function setUp(): void
    {
        $_FILES = [
            'groups' => [
                'name' => [
                    'group_1' => ['fields' => ['field_1' => ['value' => 'file_name_1']]],
                    'group_2' => [
                        'groups' => [
                            'group_2_1' => ['fields' => ['field_2' => ['value' => 'file_name_2']]],
                        ],
                    ],
                ],
                'tmp_name' => [
                    'group_1' => ['fields' => ['field_1' => ['value' => 'file_tmp_name_1']]],
                    'group_2' => [
                        'groups' => [
                            'group_2_1' => ['fields' => ['field_2' => ['value' => 'file_tmp_name_2']]],
                        ],
                    ],
                ],
            ],
        ];

        $this->_model = new RequestData();
    }

    protected function tearDown(): void
    {
        unset($this->_model);
    }

    public function testGetNameRetrievesFileName()
    {
        $this->assertEquals('file_name_1', $this->_model->getName('section_1/group_1/field_1'));
        $this->assertEquals('file_name_2', $this->_model->getName('section_1/group_2/group_2_1/field_2'));
    }

    public function testGetTmpNameRetrievesFileName()
    {
        $this->assertEquals('file_tmp_name_1', $this->_model->getTmpName('section_1/group_1/field_1'));
        $this->assertEquals('file_tmp_name_2', $this->_model->getTmpName('section_1/group_2/group_2_1/field_2'));
    }

    public function testGetNameReturnsNullIfInvalidPathIsProvided()
    {
        $this->assertNull($this->_model->getName('section_1/group_2/field_1'));
        $this->assertNull($this->_model->getName('section_1/group_3/field_1'));
        $this->assertNull($this->_model->getName('section_1/group_1/field_2'));
        $this->assertNull($this->_model->getName('section_1/group_1'));
    }
}
