<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Model\Coupon;

/**
 * SalesRule Mass Coupon Generator
 *
 * @method \Magento\SalesRule\Model\ResourceModel\Coupon getResource()
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

    /**
     * Number of attempts to generate
     */
    const MAX_GENERATE_ATTEMPTS = 10;

    /**
     * Count of generated Coupons
     * @var int
     */
    protected $generatedCount = 0;

    /**
     * @var array
     */
    protected $generatedCodes = [];

    /**
     * Sales rule coupon
     *
     * @var \Magento\SalesRule\Helper\Coupon
     */
    protected $salesRuleCoupon;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * @var \Magento\SalesRule\Model\CouponFactory
     */
    protected $couponFactory;

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
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\SalesRule\Helper\Coupon $salesRuleCoupon,
        \Magento\SalesRule\Model\CouponFactory $couponFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->salesRuleCoupon = $salesRuleCoupon;
        $this->date = $date;
        $this->couponFactory = $couponFactory;
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
        $this->_init(\Magento\SalesRule\Model\ResourceModel\Coupon::class);
    }

    /**
     * Generate coupon code
     *
     * @return string
     */
    public function generateCode()
    {
        $format = $this->getFormat();
        if (empty($format)) {
            $format = \Magento\SalesRule\Helper\Coupon::COUPON_FORMAT_ALPHANUMERIC;
        }

        $splitChar = $this->getDelimiter();
        $charset = $this->salesRuleCoupon->getCharset($format);

        $code = '';
        $charsetSize = count($charset);
        $split = max(0, (int)$this->getDash());
        $length = max(1, (int)$this->getLength());
        for ($i = 0; $i < $length; ++$i) {
            $char = $charset[\Magento\Framework\Math\Random::getRandomNumber(0, $charsetSize - 1)];
            if (($split > 0) && (($i % $split) === 0) && ($i !== 0)) {
                $char = $splitChar . $char;
            }
            $code .= $char;
        }

        return $this->getPrefix() . $code . $this->getSuffix();
    }

    /**
     * Retrieve delimiter
     *
     * @return string
     */
    public function getDelimiter()
    {
        if ($this->hasData('delimiter')) {
            return $this->getData('delimiter');
        } else {
            return $this->salesRuleCoupon->getCodeSeparator();
        }
    }

    /**
     * Generate Coupons Pool
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return $this
     */
    public function generatePool()
    {
        $this->generatedCount = 0;
        $this->generatedCodes = [];
        $size = $this->getQty();
        $maxAttempts = $this->getMaxAttempts() ? $this->getMaxAttempts() : self::MAX_GENERATE_ATTEMPTS;
        $this->increaseLength();
        /** @var $coupon \Magento\SalesRule\Model\Coupon */
        $coupon = $this->couponFactory->create();
        $nowTimestamp = $this->dateTime->formatDate($this->date->gmtTimestamp());

        for ($i = 0; $i < $size; $i++) {
            $attempt = 0;
            do {
                if ($attempt >= $maxAttempts) {
                    throw new \Magento\Framework\Exception\LocalizedException(
                        __('We cannot create the requested Coupon Qty. Please check your settings and try again.')
                    );
                }
                $code = $this->generateCode();
                ++$attempt;
            } while ($this->getResource()->exists($code));

            $expirationDate = $this->getToDate();
            if ($expirationDate instanceof \DateTimeInterface) {
                $expirationDate = $expirationDate->format('Y-m-d H:i:s');
            }

            $coupon->setId(null)
                ->setRuleId($this->getRuleId())
                ->setUsageLimit($this->getUsesPerCoupon())
                ->setUsagePerCustomer($this->getUsagePerCustomer())
                ->setExpirationDate($expirationDate)
                ->setCreatedAt($nowTimestamp)
                ->setType(\Magento\SalesRule\Helper\Coupon::COUPON_TYPE_SPECIFIC_AUTOGENERATED)
                ->setCode($code)
                ->save();

            $this->generatedCount += 1;
            $this->generatedCodes[] = $code;
        }

        return $this;
    }

    /**
     * Increase the length of Code if probability is low
     *
     * @return void
     */
    protected function increaseLength()
    {
        $maxProbability = $this->getMaxProbability() ? $this->getMaxProbability() : self::MAX_PROBABILITY_OF_GUESSING;
        $chars = count($this->salesRuleCoupon->getCharset($this->getFormat()));
        $size = $this->getQty();
        $length = (int)$this->getLength();
        $maxCodes = pow($chars, $length);
        $probability = $size / $maxCodes;

        if ($probability > $maxProbability) {
            do {
                $length++;
                $maxCodes = pow($chars, $length);
                $probability = $size / $maxCodes;
            } while ($probability > $maxProbability);
            $this->setLength($length);
        }
    }

    /**
     * Validate data input
     *
     * @param array $data
     * @return bool
     */
    public function validateData($data)
    {
        return !empty($data)
        && !empty($data['qty'])
        && !empty($data['rule_id'])
        && !empty($data['length'])
        && !empty($data['format'])
        && (int)$data['qty'] > 0
        && (int)$data['rule_id'] > 0
        && (int)$data['length'] > 0;
    }

    /**
     * Return the generated coupon codes
     *
     * @return array
     */
    public function getGeneratedCodes()
    {
        return $this->generatedCodes;
    }

    /**
     * Retrieve count of generated Coupons
     *
     * @return int
     */
    public function getGeneratedCount()
    {
        return $this->generatedCount;
    }
}
