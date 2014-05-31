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
namespace Magento\SalesRule\Model\Coupon;

/**
 * SalesRule Mass Coupon Generator
 *
 * @method \Magento\SalesRule\Model\Resource\Coupon getResource()
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Massgenerator extends \Magento\Framework\Model\AbstractModel implements
    \Magento\SalesRule\Model\Coupon\CodegeneratorInterface
{
    /**
     * Maximum probability of guessing the coupon on the first attempt
     */
    const MAX_PROBABILITY_OF_GUESSING = 0.25;

    const MAX_GENERATE_ATTEMPTS = 10;

    /**
     * Count of generated Coupons
     * @var int
     */
    protected $_generatedCount = 0;

    /**
     * Sales rule coupon
     *
     * @var \Magento\SalesRule\Helper\Coupon
     */
    protected $_salesRuleCoupon = null;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @var \Magento\SalesRule\Model\CouponFactory
     */
    protected $_couponFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime
     */
    protected $dateTime;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\SalesRule\Helper\Coupon $salesRuleCoupon
     * @param \Magento\SalesRule\Model\CouponFactory $couponFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\Stdlib\DateTime $dateTime
     * @param \Magento\Framework\Model\Resource\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\Db $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\SalesRule\Helper\Coupon $salesRuleCoupon,
        \Magento\SalesRule\Model\CouponFactory $couponFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Framework\Model\Resource\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\Db $resourceCollection = null,
        array $data = array()
    ) {
        $this->_salesRuleCoupon = $salesRuleCoupon;
        $this->_date = $date;
        $this->_couponFactory = $couponFactory;
        $this->dateTime = $dateTime;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Initialize resource
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magento\SalesRule\Model\Resource\Coupon');
    }

    /**
     * Generate coupon code
     *
     * @return string
     */
    public function generateCode()
    {
        $format = $this->getFormat();
        if (!$format) {
            $format = \Magento\SalesRule\Helper\Coupon::COUPON_FORMAT_ALPHANUMERIC;
        }
        $length = max(1, (int)$this->getLength());
        $split = max(0, (int)$this->getDash());
        $suffix = $this->getSuffix();
        $prefix = $this->getPrefix();

        $splitChar = $this->getDelimiter();
        $charset = $this->_salesRuleCoupon->getCharset($format);

        $code = '';
        $charsetSize = count($charset);
        for ($i = 0; $i < $length; $i++) {
            $char = $charset[\Magento\Framework\Math\Random::getRandomNumber(0, $charsetSize - 1)];
            if ($split > 0 && $i % $split == 0 && $i != 0) {
                $char = $splitChar . $char;
            }
            $code .= $char;
        }

        $code = $prefix . $code . $suffix;
        return $code;
    }

    /**
     * Retrieve delimiter
     *
     * @return string
     */
    public function getDelimiter()
    {
        if ($this->getData('delimiter')) {
            return $this->getData('delimiter');
        } else {
            return $this->_salesRuleCoupon->getCodeSeparator();
        }
    }

    /**
     * Generate Coupons Pool
     *
     * @throws \Magento\Framework\Model\Exception
     * @return $this
     */
    public function generatePool()
    {
        $this->_generatedCount = 0;
        $size = $this->getQty();

        $maxProbability = $this->getMaxProbability() ? $this->getMaxProbability() : self::MAX_PROBABILITY_OF_GUESSING;
        $maxAttempts = $this->getMaxAttempts() ? $this->getMaxAttempts() : self::MAX_GENERATE_ATTEMPTS;

        /** @var $coupon \Magento\SalesRule\Model\Coupon */
        $coupon = $this->_couponFactory->create();

        $chars = count($this->_salesRuleCoupon->getCharset($this->getFormat()));
        $length = (int)$this->getLength();
        $maxCodes = pow($chars, $length);
        $probability = $size / $maxCodes;
        //increase the length of Code if probability is low
        if ($probability > $maxProbability) {
            do {
                $length++;
                $maxCodes = pow($chars, $length);
                $probability = $size / $maxCodes;
            } while ($probability > $maxProbability);
            $this->setLength($length);
        }

        $now = $this->dateTime->formatDate($this->_date->gmtTimestamp());

        for ($i = 0; $i < $size; $i++) {
            $attempt = 0;
            do {
                if ($attempt >= $maxAttempts) {
                    throw new \Magento\Framework\Model\Exception(
                        __('We cannot create the requested Coupon Qty. Please check your settings and try again.')
                    );
                }
                $code = $this->generateCode();
                $attempt++;
            } while ($this->getResource()->exists($code));

            $expirationDate = $this->getToDate();
            if ($expirationDate instanceof \Zend_Date) {
                $expirationDate = $expirationDate->toString(\Magento\Framework\Stdlib\DateTime::DATETIME_INTERNAL_FORMAT);
            }

            $coupon->setId(
                null
            )->setRuleId(
                $this->getRuleId()
            )->setUsageLimit(
                $this->getUsesPerCoupon()
            )->setUsagePerCustomer(
                $this->getUsesPerCustomer()
            )->setExpirationDate(
                $expirationDate
            )->setCreatedAt(
                $now
            )->setType(
                \Magento\SalesRule\Helper\Coupon::COUPON_TYPE_SPECIFIC_AUTOGENERATED
            )->setCode(
                $code
            )->save();

            $this->_generatedCount++;
        }
        return $this;
    }

    /**
     * Validate input
     *
     * @param array $data
     * @return bool
     */
    public function validateData($data)
    {
        return !empty($data) &&
            !empty($data['qty']) &&
            !empty($data['rule_id']) &&
            !empty($data['length']) &&
            !empty($data['format']) &&
            (int)$data['qty'] > 0 &&
            (int)$data['rule_id'] > 0 &&
            (int)$data['length'] > 0;
    }

    /**
     * Retrieve count of generated Coupons
     *
     * @return int
     */
    public function getGeneratedCount()
    {
        return $this->_generatedCount;
    }
}
