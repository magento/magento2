<?php
/**
 * Google Analytics Tracking Id Backend model
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleAnalytics\Model\Config\Backend;

/**
 * @api
 * @since 100.0.2
 */
class TrackingId extends \Magento\GoogleAnalytics\Model\Config\Backend\AbstractId
{
    /**
     * Validation rule Transaction id
     *
     * @return \Zend_Validate_Interface|null
     */
    protected function _getValidationRulesBeforeSave()
    {
        $this->_validatorComposite->addRule(
            $this->_validatorFactory->createTrackingIdValidator($this->getValue()),
            'tracking_id'
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
