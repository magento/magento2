<?php
/**
 * Copyright 2013 Adobe
 * All Rights Reserved.
 */
namespace Magento\GoogleAdwords\Model\Config\Backend;

use Laminas\Validator\ValidatorInterface;

/**
 * @api
 * @since 100.0.2
 */
class ConversionId extends AbstractConversion
{
    /**
     * Validation rule conversion id
     *
     * @return ValidatorInterface|null
     */
    protected function _getValidationRulesBeforeSave()
    {
        $this->_validatorComposite->addRule(
            $this->_validatorFactory->createConversionIdValidator($this->getValue()),
            'conversion_id'
        );
        return $this->_validatorComposite;
    }

    /**
     * Get tested value
     *
     * @return string
     */
    public function getConversionId()
    {
        return $this->getValue();
    }
}
