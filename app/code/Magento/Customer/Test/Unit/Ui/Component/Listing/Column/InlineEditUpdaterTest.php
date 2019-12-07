<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Test\Unit\Ui\Component\Listing\Column;

use Magento\Customer\Ui\Component\Listing\Column\ValidationRules;
use Magento\Customer\Ui\Component\Listing\Column\InlineEditUpdater;
use Magento\Customer\Api\Data\ValidationRuleInterface;

class InlineEditUpdaterTest extends \PHPUnit\Framework\TestCase
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
        $this->validationRules = $this->getMockBuilder(
            \Magento\Customer\Ui\Component\Listing\Column\ValidationRules::class
        )->disableOriginalConstructor()->getMock();

        $this->validationRule = $this->getMockBuilder(\Magento\Customer\Api\Data\ValidationRuleInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->column = $this->getMockBuilder(\Magento\Framework\View\Element\UiComponentInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->component = new InlineEditUpdater($this->validationRules);
    }

    public function testApplyEditing()
    {
        $this->column->expects($this->once())
            ->method('getConfiguration')
            ->willReturn([
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
