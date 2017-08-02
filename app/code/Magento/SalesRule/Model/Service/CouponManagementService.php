<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Model\Service;

/**
 * Coupon management service class
 *
 * @since 2.0.0
 */
class CouponManagementService implements \Magento\SalesRule\Api\CouponManagementInterface
{
    /**
     * @var \Magento\SalesRule\Model\CouponFactory
     * @since 2.0.0
     */
    protected $couponFactory;

    /**
     * @var \Magento\SalesRule\Model\RuleFactory
     * @since 2.0.0
     */
    protected $ruleFactory;

    /**
     * @var \Magento\SalesRule\Model\ResourceModel\Coupon\CollectionFactory
     * @since 2.0.0
     */
    protected $collectionFactory;

    /**
     * @var \Magento\SalesRule\Model\Coupon\Massgenerator
     * @since 2.0.0
     */
    protected $couponGenerator;

    /**
     * @var \Magento\SalesRule\Model\Spi\CouponResourceInterface
     * @since 2.0.0
     */
    protected $resourceModel;

    /**
     * var \Magento\SalesRule\Api\Data\CouponMassDeleteResultInterfaceFactory
     * @since 2.0.0
     */
    protected $couponMassDeleteResultFactory;

    /**
     * @param \Magento\SalesRule\Model\CouponFactory $couponFactory
     * @param \Magento\SalesRule\Model\RuleFactory $ruleFactory
     * @param \Magento\SalesRule\Model\ResourceModel\Coupon\CollectionFactory $collectionFactory
     * @param \Magento\SalesRule\Model\Coupon\Massgenerator $couponGenerator
     * @param \Magento\SalesRule\Model\Spi\CouponResourceInterface $resourceModel
     * @param \Magento\SalesRule\Api\Data\CouponMassDeleteResultInterfaceFactory $couponMassDeleteResultFactory
     * @since 2.0.0
     */
    public function __construct(
        \Magento\SalesRule\Model\CouponFactory $couponFactory,
        \Magento\SalesRule\Model\RuleFactory $ruleFactory,
        \Magento\SalesRule\Model\ResourceModel\Coupon\CollectionFactory $collectionFactory,
        \Magento\SalesRule\Model\Coupon\Massgenerator $couponGenerator,
        \Magento\SalesRule\Model\Spi\CouponResourceInterface $resourceModel,
        \Magento\SalesRule\Api\Data\CouponMassDeleteResultInterfaceFactory $couponMassDeleteResultFactory
    ) {
        $this->couponFactory = $couponFactory;
        $this->ruleFactory = $ruleFactory;
        $this->collectionFactory = $collectionFactory;
        $this->couponGenerator = $couponGenerator;
        $this->resourceModel = $resourceModel;
        $this->couponMassDeleteResultFactory = $couponMassDeleteResultFactory;
    }

    /**
     * Generate coupon for a rule
     *
     * @param \Magento\SalesRule\Api\Data\CouponGenerationSpecInterface $couponSpec
     * @return string[]
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.0.0
     */
    public function generate(\Magento\SalesRule\Api\Data\CouponGenerationSpecInterface $couponSpec)
    {
        $data = $this->convertCouponSpec($couponSpec);
        if (!$this->couponGenerator->validateData($data)) {
            throw new \Magento\Framework\Exception\InputException();
        }

        try {
            $rule = $this->ruleFactory->create()->load($couponSpec->getRuleId());
            if (!$rule->getRuleId()) {
                throw \Magento\Framework\Exception\NoSuchEntityException::singleField(
                    \Magento\SalesRule\Model\Coupon::KEY_RULE_ID,
                    $couponSpec->getRuleId()
                );
            }
            if (!$rule->getUseAutoGeneration()) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('Specified rule does not allow automatic coupon generation')
                );
            }

            $this->couponGenerator->setData($data);
            $this->couponGenerator->setData('to_date', $rule->getToDate());
            $this->couponGenerator->setData('uses_per_coupon', $rule->getUsesPerCoupon());
            $this->couponGenerator->setData('usage_per_customer', $rule->getUsesPerCustomer());

            $this->couponGenerator->generatePool();
            return $this->couponGenerator->getGeneratedCodes();
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Error occurred when generating coupons: %1', $e->getMessage())
            );
        }
    }

    /**
     * Convert CouponGenerationSpecInterface to data array expected by Massgenerator
     *
     * @param \Magento\SalesRule\Api\Data\CouponGenerationSpecInterface $couponSpec
     * @return array
     * @since 2.0.0
     */
    protected function convertCouponSpec(\Magento\SalesRule\Api\Data\CouponGenerationSpecInterface $couponSpec)
    {
        $data = [];
        $data['rule_id'] = $couponSpec->getRuleId();
        $data['qty'] = $couponSpec->getQuantity();
        $data['format'] = $couponSpec->getFormat();
        $data['length'] = $couponSpec->getLength();
        $data['prefix'] = $couponSpec->getPrefix();
        $data['suffix'] = $couponSpec->getSuffix();
        $data['dash'] = $couponSpec->getDelimiterAtEvery();

        //ensure we have a format
        if (empty($data['format'])) {
            $data['format'] = $couponSpec::COUPON_FORMAT_ALPHANUMERIC;
        }

        //if specified, use the supplied delimiter
        if ($couponSpec->getDelimiter()) {
            $data['delimiter'] = $couponSpec->getDelimiter();
        }
        return $data;
    }

    /**
     * Delete coupon by coupon ids.
     *
     * @param int[] $ids
     * @param bool $ignoreInvalidCoupons
     * @return \Magento\SalesRule\Api\Data\CouponMassDeleteResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.0.0
     */
    public function deleteByIds(array $ids, $ignoreInvalidCoupons = true)
    {
        return $this->massDelete('coupon_id', $ids, $ignoreInvalidCoupons);
    }

    /**
     * Delete coupon by coupon codes.
     *
     * @param string[] codes
     * @param bool $ignoreInvalidCoupons
     * @return \Magento\SalesRule\Api\Data\CouponMassDeleteResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.0.0
     */
    public function deleteByCodes(array $codes, $ignoreInvalidCoupons = true)
    {
        return $this->massDelete('code', $codes, $ignoreInvalidCoupons);
    }

    /**
     * Delete coupons by filter
     *
     * @param string $fieldName
     * @param string[] fieldValues
     * @param bool $ignoreInvalid
     * @return \Magento\SalesRule\Api\Data\CouponMassDeleteResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.0.0
     */
    protected function massDelete($fieldName, array $fieldValues, $ignoreInvalid)
    {
        $couponsCollection = $this->collectionFactory->create()
            ->addFieldToFilter(
                $fieldName,
                ['in' => $fieldValues]
            );

        if (!$ignoreInvalid) {
            if ($couponsCollection->getSize() != count($fieldValues)) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Some coupons are invalid.'));
            }
        }

        $results = $this->couponMassDeleteResultFactory->create();
        $failedItems = [];
        $fieldValues = array_flip($fieldValues);
        /** @var \Magento\SalesRule\Model\Coupon $coupon */
        foreach ($couponsCollection->getItems() as $coupon) {
            $couponValue = ($fieldName == 'code') ? $coupon->getCode() : $coupon->getCouponId();
            try {
                $coupon->delete();
            } catch (\Exception $e) {
                $failedItems[] = $couponValue;
            }
            unset($fieldValues[$couponValue]);
        }
        $results->setFailedItems($failedItems);
        $results->setMissingItems(array_flip($fieldValues));
        return $results;
    }
}
