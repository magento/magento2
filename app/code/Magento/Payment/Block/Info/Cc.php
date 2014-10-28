<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Payment\Block\Info;

/**
 * Credit card generic payment info
 */
class Cc extends \Magento\Payment\Block\Info
{
    /**
     * Payment config model
     *
     * @var \Magento\Payment\Model\Config
     */
    protected $_paymentConfig;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Payment\Model\Config $paymentConfig
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Payment\Model\Config $paymentConfig,
        array $data = array()
    ) {
        parent::__construct($context, $data);
        $this->_paymentConfig = $paymentConfig;
    }

    /**
     * Retrieve credit card type name
     *
     * @return string
     */
    public function getCcTypeName()
    {
        $types = $this->_paymentConfig->getCcTypes();
        $ccType = $this->getInfo()->getCcType();
        if (isset($types[$ccType])) {
            return $types[$ccType];
        }
        return empty($ccType) ? __('N/A') : $ccType;
    }

    /**
     * Whether current payment method has credit card expiration info
     *
     * @return bool
     */
    public function hasCcExpDate()
    {
        return (int)$this->getInfo()->getCcExpMonth() || (int)$this->getInfo()->getCcExpYear();
    }

    /**
     * Retrieve CC expiration month
     *
     * @return string
     */
    public function getCcExpMonth()
    {
        $month = $this->getInfo()->getCcExpMonth();
        if ($month < 10) {
            $month = '0' . $month;
        }
        return $month;
    }

    /**
     * Retrieve CC expiration date
     *
     * @return \Magento\Framework\Stdlib\DateTime\Date
     */
    public function getCcExpDate()
    {
        $date = $this->_localeDate->date(0);
        $date->setYear($this->getInfo()->getCcExpYear());
        $date->setMonth($this->getInfo()->getCcExpMonth());
        return $date;
    }

    /**
     * Prepare credit card related payment info
     *
     * @param \Magento\Framework\Object|array $transport
     * @return \Magento\Framework\Object
     */
    protected function _prepareSpecificInformation($transport = null)
    {
        if (null !== $this->_paymentSpecificInformation) {
            return $this->_paymentSpecificInformation;
        }
        $transport = parent::_prepareSpecificInformation($transport);
        $data = array();
        if ($ccType = $this->getCcTypeName()) {
            $data[__('Credit Card Type')] = $ccType;
        }
        if ($this->getInfo()->getCcLast4()) {
            $data[__('Credit Card Number')] = sprintf('xxxx-%s', $this->getInfo()->getCcLast4());
        }
        if (!$this->getIsSecureMode()) {
            if ($ccSsIssue = $this->getInfo()->getCcSsIssue()) {
                $data[__('Switch/Solo/Maestro Issue Number')] = $ccSsIssue;
            }
            $year = $this->getInfo()->getCcSsStartYear();
            $month = $this->getInfo()->getCcSsStartMonth();
            if ($year && $month) {
                $data[__('Switch/Solo/Maestro Start Date')] = $this->_formatCardDate($year, $month);
            }
        }
        return $transport->setData(array_merge($data, $transport->getData()));
    }

    /**
     * Format year/month on the credit card
     *
     * @param string $year
     * @param string $month
     * @return string
     */
    protected function _formatCardDate($year, $month)
    {
        return sprintf('%s/%s', sprintf('%02d', $month), $year);
    }
}
