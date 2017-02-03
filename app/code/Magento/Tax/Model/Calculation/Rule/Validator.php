<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Tax\Model\Calculation\Rule;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Tax\Model\ClassModel as TaxClassModel;
use Magento\Tax\Model\ClassModelRegistry;
use Zend_Validate_Exception;

class Validator extends \Magento\Framework\Validator\AbstractValidator
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
     * @throws Zend_Validate_Exception If validation of $value is impossible
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    public function isValid($value)
    {
        $messages = [];

        // Position is required and must be 0 or greater
        if (!\Zend_Validate::is(trim($value->getPosition()), 'NotEmpty')) {
            $this->addErrorMessage($messages, InputException::REQUIRED_FIELD, ['fieldName' => 'position']);
        }
        if (!\Zend_Validate::is(trim($value->getPosition()), 'GreaterThan', [-1])) {
            $this->addErrorMessage(
                $messages,
                InputException::INVALID_FIELD_MIN_VALUE,
                ['fieldName' => 'position', 'value' => $value->getPosition(), 'minValue' => 0]
            );
        }

        // Priority is required and must be 0 or greater
        if (!\Zend_Validate::is(trim($value->getPriority()), 'NotEmpty')) {
            $this->addErrorMessage($messages, InputException::REQUIRED_FIELD, ['fieldName' => 'priority']);
        }
        if (!\Zend_Validate::is(trim($value->getPriority()), 'GreaterThan', [-1])) {
            $this->addErrorMessage(
                $messages,
                InputException::INVALID_FIELD_MIN_VALUE,
                ['fieldName' => 'priority', 'value' => $value->getPriority(), 'minValue' => 0]
            );
        }

        // Code is required
        if (!\Zend_Validate::is(trim($value->getCode()), 'NotEmpty')) {
            $this->addErrorMessage($messages, InputException::REQUIRED_FIELD, ['fieldName' => 'code']);
        }

        // customer tax class ids is required
        if (($value->getCustomerTaxClassIds() === null) || !$value->getCustomerTaxClassIds()) {
            $this->addErrorMessage(
                $messages,
                InputException::REQUIRED_FIELD,
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
                            NoSuchEntityException::MESSAGE_SINGLE_FIELD,
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
            $this->addErrorMessage($messages, InputException::REQUIRED_FIELD, ['fieldName' => 'product_tax_class_ids']);
        } else { // see if the product tax class ids exist
            $productTaxClassIds = $value->getProductTaxClassIds();
            foreach ($productTaxClassIds as $productTaxClassId) {
                try {
                    $taxClass = $this->classModelRegistry->retrieve($productTaxClassId);
                    if ($taxClass === null || !($taxClass->getClassType() == TaxClassModel::TAX_CLASS_TYPE_PRODUCT)) {
                        $this->addErrorMessage(
                            $messages,
                            NoSuchEntityException::MESSAGE_SINGLE_FIELD,
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
            $this->addErrorMessage($messages, InputException::REQUIRED_FIELD, ['fieldName' => 'tax_rate_ids']);
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
