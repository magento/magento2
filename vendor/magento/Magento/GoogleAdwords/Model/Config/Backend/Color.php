<?php
/**
 * Google AdWords Color Backend model
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\GoogleAdwords\Model\Config\Backend;

class Color extends \Magento\GoogleAdwords\Model\Config\Backend\AbstractConversion
{
    /**
     * Validation rule conversion color
     *
     * @return \Zend_Validate_Interface|null
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
