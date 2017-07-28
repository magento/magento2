<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing;

use Magento\CatalogImportExport\Model\Import\Product\RowValidatorInterface;
use \Magento\Framework\Validator\AbstractValidator;

/**
 * Class \Magento\AdvancedPricingImportExport\Model\Import\AdvancedPricing\Validator
 *
 * @since 2.0.0
 */
class Validator extends AbstractValidator implements RowValidatorInterface
{
    /**
     * @var RowValidatorInterface[]|AbstractValidator[]
     * @since 2.0.0
     */
    protected $validators = [];

    /**
     * @param RowValidatorInterface[] $validators
     * @since 2.0.0
     */
    public function __construct($validators = [])
    {
        $this->validators = $validators;
    }

    /**
     * Check value is valid
     *
     * @param array $value
     * @return bool
     * @since 2.0.0
     */
    public function isValid($value)
    {
        $returnValue = true;
        $this->_clearMessages();
        foreach ($this->validators as $validator) {
            if (!$validator->isValid($value)) {
                $returnValue = false;
                $this->_addMessages($validator->getMessages());
            }
        }
        return $returnValue;
    }

    /**
     * @param \Magento\CatalogImportExport\Model\Import\Product $context
     * @return $this
     * @since 2.0.0
     */
    public function init($context)
    {
        foreach ($this->validators as $validator) {
            $validator->init($context);
        }
        return $this;
    }
}
