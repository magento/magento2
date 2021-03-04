<?php
/**
 * Google Analytics Measurement Id Backend model
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleAnalytics\Model\Config\Backend;

/**
 * @api
 * @since 100.0.2
 */
class MeasurementId extends \Magento\GoogleAnalytics\Model\Config\Backend\AbstractId
{
    /**
     * Validation rule conversion id
     *
     * @return \Zend_Validate_Interface|null
     */
    protected function _getValidationRulesBeforeSave()
    {
        $this->_validatorComposite->addRule(
            $this->_validatorFactory->createMeasurementIdValidator($this->getValue()),
            'measurement_id'
        );
        return $this->_validatorComposite;
    }

    /**
     * Get tested value
     *
     * @return string
     */
    public function getAccountId()
    {
        return $this->getValue();
    }
}
