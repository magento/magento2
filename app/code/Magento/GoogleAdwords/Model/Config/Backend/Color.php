<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleAdwords\Model\Config\Backend;

use Laminas\Validator\ValidatorInterface;

/**
 * @api
 * @since 100.0.2
 */
class Color extends AbstractConversion
{
    /**
     * Validation rule conversion color
     *
     * @return ValidatorInterface|null
     */
    protected function _getValidationRulesBeforeSave()
    {
        $this->_validatorComposite->addRule(
            $this->_validatorFactory->createColorValidator($this->getValue()),
            'conversion_color'
        );
        return $this->_validatorComposite;
    }

    /**
     * Get tested value
     *
     * @return string
     */
    public function getConversionColor()
    {
        return $this->getValue();
    }
}
