<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Validation State Model for JCB
 */
namespace Magento\Centinel\Model\State;

class Jcb extends \Magento\Centinel\Model\AbstractState
{
    /**
     * Analyse lookup`s results. If it has require params for authenticate, return true
     *
     * @return bool
     */
    public function isAuthenticateAllowed()
    {
        return $this->_isLookupStrictSuccessful() && is_null($this->getAuthenticateEciFlag());
    }

    /**
     * Analyse authenticate`s results. If authenticate is successful return true and false if it failure
     * Result depends from flag self::getIsModeStrict()
     *
     * @return bool
     */
    public function isAuthenticateSuccessful()
    {
        if (!$this->getIsModeStrict() && $this->_isLookupSoftSuccessful()) {
            return true;
        }

        if ($this->_isLookupStrictSuccessful()) {
            if ($this->_isAuthenticationSuccessful()) {
                return true;
            }
            if ($this->_isAuthenticationUnavailable() && !$this->getIsModeStrict()) {
                return true;
            }
            if ($this->_isAuthenticationAttemptsPerformed()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Returns true if authentication successful (Test case 1)
     *
     * @return bool
     */
    protected function _isAuthenticationSuccessful()
    {
        return $this->getAuthenticatePaResStatus() === 'Y' &&
            $this->getAuthenticateEciFlag() === '05' &&
            $this->getAuthenticateXid() != '' &&
            $this->getAuthenticateCavv() != '' &&
            $this->getAuthenticateErrorNo() === '0' &&
            $this->getAuthenticateSignatureVerification() === 'Y';
    }

    /**
     * Returns true if authentication unavailable (Test case 4) or timeout encountered (Test case 5)
     *
     * @return bool
     */
    protected function _isAuthenticationUnavailable()
    {
        return $this->getAuthenticatePaResStatus() === 'U' &&
            $this->getAuthenticateSignatureVerification() === 'Y' &&
            $this->getAuthenticateEciFlag() === '07' &&
            $this->getAuthenticateXid() != '' &&
            $this->getAuthenticateCavv() === '' &&
            $this->getAuthenticateErrorNo() === '0';
    }

    /**
     * Returns true if processing attempts performed (Test case 11)
     *
     * @return bool
     */
    protected function _isAuthenticationAttemptsPerformed()
    {
        return $this->getAuthenticatePaResStatus() === 'A' &&
            $this->getAuthenticateSignatureVerification() === 'Y' &&
            $this->getAuthenticateEciFlag() === '06' &&
            $this->getAuthenticateXid() != '' &&
            $this->getAuthenticateCavv() != '' &&
            $this->getAuthenticateErrorNo() === '0';
    }

    /**
     * Analyse lookup`s results. If lookup is strict successful return true (Test cases 1-4, 6, 10-11)
     *
     * @return bool
     */
    protected function _isLookupStrictSuccessful()
    {
        return $this->getLookupEnrolled() === 'Y' &&
            $this->getLookupAcsUrl() != '' &&
            $this->getLookupPayload() != '' &&
            $this->getLookupErrorNo() === '0';
    }

    /**
     * Analyse lookup`s results. If lookup is soft successful return true (Test cases 5,7,8,9)
     *
     * @return bool
     */
    protected function _isLookupSoftSuccessful()
    {
        $acsUrl = $this->getLookupAcsUrl();
        $payload = $this->getLookupPayload();
        $errorNo = $this->getLookupErrorNo();
        $enrolled = $this->getLookupEnrolled();

        if ($acsUrl !== '' || $payload !== '') {
            return false;
        }

        if ($enrolled === '' && $errorNo === '0') {
            return true;
        }

        if ($enrolled === 'U' && ($errorNo === '0' || $errorNo !== '')) {
            return true;
        }

        return false;
    }
}
