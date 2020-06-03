<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Eav\Test\Unit\Model\Adminhtml\System\Config\Source;

use Magento\Eav\Model\Adminhtml\System\Config\Source\Inputtype;
use PHPUnit\Framework\TestCase;

class InputtypeTest extends TestCase
{
    /**
     * @var Inputtype
     */
    protected $model;

    protected function setUp(): void
    {
        $this->model = new Inputtype(
            $this->getOptionsArray()
        );
    }

    public function testToOptionArray()
    {
        $expectedResult = $this->getOptionsArray();
        $this->assertEquals($expectedResult, $this->model->toOptionArray());
    }

    /**
     * @return array
     */
    private function getOptionsArray()
    {
        return [
            ['value' => 'text', 'label' => 'Text Field'],
            ['value' => 'textarea', 'label' => 'Text Area'],
            ['value' => 'texteditor', 'label' => 'Text Editor'],
            ['value' => 'date', 'label' => 'Date'],
            ['value' => 'boolean', 'label' => 'Yes/No'],
            ['value' => 'multiselect', 'label' => 'Multiple Select'],
            ['value' => 'select', 'label' => 'Dropdown']
        ];
    }
}
