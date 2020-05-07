<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Ui\Component\Listing\Column;

use Magento\Customer\Api\Data\ValidationRuleInterface;
use Magento\Customer\Ui\Component\Listing\Column\InlineEditUpdater;
use Magento\Customer\Ui\Component\Listing\Column\ValidationRules;
use Magento\Framework\View\Element\UiComponentInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class InlineEditUpdaterTest extends TestCase
{
    /** @var ValidationRules|MockObject  */
    protected $validationRules;

    /** @var ValidationRuleInterface|MockObject */
    protected $validationRule;

    /** @var UiComponentInterface|MockObject */
    protected $column;

    /** @var InlineEditUpdater */
    protected $component;

    protected function setUp(): void
    {
        $this->validationRules = $this->getMockBuilder(
            ValidationRules::class
        )->disableOriginalConstructor()
            ->getMock();

        $this->validationRule = $this->getMockBuilder(ValidationRuleInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->column = $this->getMockBuilder(UiComponentInterface::class)
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

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
