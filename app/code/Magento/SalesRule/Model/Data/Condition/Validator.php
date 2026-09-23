<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\SalesRule\Model\Data\Condition;

use InvalidArgumentException;
use Magento\Framework\Validator\AbstractValidator;
use Magento\SalesRule\Model\Data\Condition;
use Magento\SalesRule\Model\Data\Rule;

class Validator extends AbstractValidator
{
    /**
     * @inheritDoc
     */
    public function isValid($value)
    {
        $this->_clearMessages();
        if (!$value instanceof Rule) {
            throw new InvalidArgumentException('Expected instance of ' . Rule::class);
        }
        if ($value->getCondition()) {
            $this->validate($value->getCondition());
        }
        if ($value->getActionCondition()) {
            $this->validate($value->getActionCondition());
        }
        return empty($this->getMessages());
    }

    /**
     * Validate condition attributes
     *
     * @param Condition $condition
     * @return void
     */
    private function validate(Condition $condition): void
    {
        $scope = $condition->getExtensionAttributes()?->getAttributeScope();
        if ($scope && !in_array($scope, ['parent', 'children'], true)) {
            $this->_addMessages([__(
                'Invalid value of "%value" provided for the %fieldName field.',
                ['fieldName' => 'attribute_scope', 'value' => $scope]
            )]);
        }
        if ($condition->getConditions()) {
            foreach ($condition->getConditions() as $condition) {
                $this->validate($condition);
            }
        }
    }
}
