<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Centinel module base helper
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Centinel\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Return label for cmpi field
     *
     * @param string $fieldName
     * @return string
     */
    public function getCmpiLabel($fieldName)
    {
        switch ($fieldName) {
            case \Magento\Centinel\Model\Service::CMPI_PARES:
                return __('3D Secure Verification Result');
            case \Magento\Centinel\Model\Service::CMPI_ENROLLED:
                return __('3D Secure Cardholder Validation');
            case \Magento\Centinel\Model\Service::CMPI_ECI:
                return __('3D Secure Electronic Commerce Indicator');
            case \Magento\Centinel\Model\Service::CMPI_CAVV:
                return __('3D Secure CAVV');
            case \Magento\Centinel\Model\Service::CMPI_XID:
                return __('3D Secure XID');
        }
        return '';
    }

    /**
     * Return value for cmpi field
     *
     * @param string $fieldName
     * @param string $value
     * @return string
     */
    public function getCmpiValue($fieldName, $value)
    {
        switch ($fieldName) {
            case \Magento\Centinel\Model\Service::CMPI_PARES:
                return $this->_getCmpiParesValue($value);
            case \Magento\Centinel\Model\Service::CMPI_ENROLLED:
                return $this->_getCmpiEnrolledValue($value);
            case \Magento\Centinel\Model\Service::CMPI_ECI:
                return $this->_getCmpiEciValue($value);
            case \Magento\Centinel\Model\Service::CMPI_CAVV:
                // break intentionally omitted
            case \Magento\Centinel\Model\Service::CMPI_XID:
                return $value;
        }
        return '';
    }

    /**
     * Return text value for cmpi eci flag field
     *
     * @param string $value
     * @return string
     */
    private function _getCmpiEciValue($value)
    {
        switch ($value) {
            case '01':
            case '07':
                return __('Merchant Liability');
            case '02':
            case '05':
            case '06':
                return __('Card Issuer Liability');
            default:
                return $value;
        }
    }

    /**
     * Return text value for cmpi enrolled field
     *
     * @param string $value
     * @return string
     */
    private function _getCmpiEnrolledValue($value)
    {
        switch ($value) {
            case 'Y':
                return __('Enrolled');
            case 'U':
                return __('Enrolled but Authentication Unavailable');
            case 'N':
                // break intentionally omitted
            default:
                return __('Not Enrolled');
        }
    }

    /**
     * Return text value for cmpi pares field
     *
     * @param string $value
     * @return string
     */
    private function _getCmpiParesValue($value)
    {
        switch ($value) {
            case 'Y':
                return __('Successful');
            case 'N':
                return __('Failed');
            case 'U':
                return __('Unable to complete');
            case 'A':
                return __('Successful attempt');
            default:
                return $value;
        }
    }

    /**
     * Return url of page about visa verification
     *
     * @return string
     */
    public function getVisaLearnMorePageUrl()
    {
        return 'https://usa.visa.com/personal/security/vbv/index.html?ep=v_sym_verifiedbyvisa';
    }

    /**
     * Return url of page about mastercard verification
     *
     * @return string
     */
    public function getMastercardLearnMorePageUrl()
    {
        return 'http://www.mastercardbusiness.com/mcbiz/index.jsp?template=/orphans&amp;content=securecodepopup';
    }
}
