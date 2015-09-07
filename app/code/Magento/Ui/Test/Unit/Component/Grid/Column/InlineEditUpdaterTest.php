<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Test\Unit\Component\Grid\Column;

use Magento\Ui\Component\Grid\Column\ValidationRules;
use Magento\Ui\Component\Grid\Column\InlineEditUpdater;
use Magento\Customer\Api\Data\ValidationRuleInterface;

class InlineEditUpdaterTest extends \PHPUnit_Framework_TestCase
{
    /** @var ValidationRules|\PHPUnit_Framework_MockObject_MockObject  */
    protected $validationRules;

    /** @var ValidationRuleInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $validationRule;

    /** @var \Magento\Framework\View\Element\UiComponentInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $column;

    /** @var InlineEditUpdater */
    protected $component;

    protected function setUp()
    {
        $this->validationRules = $this->getMockBuilder('Magento\Ui\Component\Grid\Column\ValidationRules')
            ->disableOriginalConstructor()
            ->getMock();

        $this->validationRule = $this->getMockBuilder('Magento\Customer\Api\Data\ValidationRuleInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->column = $this->getMockBuilder('Magento\Framework\View\Element\UiComponentInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $this->component = new InlineEditUpdater($this->validationRules);
    }

    public function testApplyEditing()
    {
        $this->column->expects($this->once())
            ->method('getConfiguration')
            ->willReturn([
                'dataType' => 'text',
                'visible' => true,
            ]);
        $this->validationRules->expects($this->once())
            ->method('getValidationRules')
            ->with(true, [$this->validationRule])
            ->willReturn([
                'validate-email' => true,
                'required-entry' => true
            ]);
        $this->column->expects($this->once())
            ->method('setData')
            ->with(
                'config',
                [
                    'dataType' => 'text',
                    'visible' => true,
                    'editor' => [
                        'editorType' => 'text',
                        'validation' => [
                            'validate-email' => true,
                            'required-entry' => true,
                        ]
                    ]
                ]
            );

        $this->component->applyEditing($this->column, 'text', [$this->validationRule], true);
    }
}
