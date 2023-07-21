<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Model\Calculation\Rule;

use Laminas\Validator\GreaterThan;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Validator\AbstractValidator;
use Magento\Framework\Validator\NotEmpty;
use Magento\Framework\Validator\ValidateException;
use Magento\Framework\Validator\ValidatorChain;
use Magento\Tax\Model\ClassModel as TaxClassModel;
use Magento\Tax\Model\ClassModelRegistry;

class Validator extends AbstractValidator
{
    /**
     * @var ClassModelRegistry
     */
    protected $classModelRegistry;

    /**
     * @param ClassModelRegistry $classModelRegistry
     */
    public function __construct(ClassModelRegistry $classModelRegistry)
    {
        $this->classModelRegistry = $classModelRegistry;
    }

    /**
     * Validate rule model
     *
     * @param \Magento\Tax\Model\Calculation\Rule $value
     * @return boolean
     * @throws ValidateException If validation of $value is impossible
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function isValid($value)
    {
        $messages = [];

        // Position is required and must be 0 or greater
        if (!ValidatorChain::is(trim($value->getPosition()), NotEmpty::class)) {
            $this->addErrorMessage(
                $messages,
                '"%fieldName" is required. Enter and try again.',
                ['fieldName' => 'position']
            );
        }
        if (!ValidatorChain::is(trim($value->getPosition()), GreaterThan::class, [-1])) {
            $this->addErrorMessage(
                $messages,
                'The %fieldName value of "%value" must be greater than or equal to %minValue.',
                ['fieldName' => 'position', 'value' => $value->getPosition(), 'minValue' => 0]
            );
        }

        // Priority is required and must be 0 or greater
        if (!ValidatorChain::is(trim($value->getPriority() ?? ''), NotEmpty::class)) {
            $this->addErrorMessage(
                $messages,
                '"%fieldName" is required. Enter and try again.',
                ['fieldName' => 'priority']
            );
        }
        if (!ValidatorChain::is(trim($value->getPriority() ?? ''), GreaterThan::class, [-1])) {
            $this->addErrorMessage(
                $messages,
                'The %fieldName value of "%value" must be greater than or equal to %minValue.',
                ['fieldName' => 'priority', 'value' => $value->getPriority(), 'minValue' => 0]
            );
        }

        // Code is required
        if ($value->getCode() === null || !ValidatorChain::is(trim($value->getCode() ?? ''), NotEmpty::class)) {
            $this->addErrorMessage(
                $messages,
                '"%fieldName" is required. Enter and try again.',
                ['fieldName' => 'code']
            );
        }

        // customer tax class ids is required
        if (($value->getCustomerTaxClassIds() === null) || !$value->getCustomerTaxClassIds()) {
            $this->addErrorMessage(
                $messages,
                '"%fieldName" is required. Enter and try again.',
                ['fieldName' => 'customer_tax_class_ids']
            );
        } else { // see if the customer tax class ids exist
            $customerTaxClassIds = $value->getCustomerTaxClassIds();
            foreach ($customerTaxClassIds as $customerTaxClassId) {
                try {
                    $taxClass = $this->classModelRegistry->retrieve($customerTaxClassId);
                    if ($taxClass === null || !($taxClass->getClassType() == TaxClassModel::TAX_CLASS_TYPE_CUSTOMER)) {
                        $this->addErrorMessage(
                            $messages,
                            'No such entity with %fieldName = %fieldValue',
                            [
                                'fieldName' => 'customer_tax_class_ids',
                                'value'     => $customerTaxClassId,
                            ]
                        );
                    }
                } catch (NoSuchEntityException $e) {
                    $this->addErrorMessage(
                        $messages,
                        $e->getRawMessage(),
                        $e->getParameters()
                    );
                }
            }
        }

        // product tax class ids is required
        if (($value->getProductTaxClassIds() === null) || !$value->getProductTaxClassIds()) {
            $this->addErrorMessage(
                $messages,
                '"%fieldName" is required. Enter and try again.',
                ['fieldName' => 'product_tax_class_ids']
            );
        } else { // see if the product tax class ids exist
            $productTaxClassIds = $value->getProductTaxClassIds();
            foreach ($productTaxClassIds as $productTaxClassId) {
                try {
                    $taxClass = $this->classModelRegistry->retrieve($productTaxClassId);
                    if ($taxClass === null || !($taxClass->getClassType() == TaxClassModel::TAX_CLASS_TYPE_PRODUCT)) {
                        $this->addErrorMessage(
                            $messages,
                            'No such entity with %fieldName = %fieldValue',
                            [
                                'fieldName' => 'product_tax_class_ids',
                                'value'     => $productTaxClassId,
                            ]
                        );
                    }
                } catch (NoSuchEntityException $e) {
                    $this->addErrorMessage(
                        $messages,
                        $e->getRawMessage(),
                        $e->getParameters()
                    );
                }
            }
        }

        // tax rate ids is required
        if (($value->getTaxRateIds() === null) || !$value->getTaxRateIds()) {
            $this->addErrorMessage(
                $messages,
                '"%fieldName" is required. Enter and try again.',
                ['fieldName' => 'tax_rate_ids']
            );
        }
        $this->_addMessages($messages);
        return empty($messages);
    }

    /**
     * Format error message
     *
     * @param string[] $messages
     * @param string $message
     * @param array $params
     * @return void
     */
    protected function addErrorMessage(&$messages, $message, $params)
    {
        $messages[$params['fieldName']] = __($message, $params);
    }
}
