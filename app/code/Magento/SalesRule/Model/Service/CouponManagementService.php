<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Model\Service;

use Magento\SalesRule\Model\Coupon;

/**
 * Coupon management service class
 *
 */
class CouponManagementService implements \Magento\SalesRule\Api\CouponManagementInterface
{
    /**
     * @var \Magento\SalesRule\Model\CouponFactory
     */
    protected $couponFactory;

    /**
     * @var \Magento\SalesRule\Model\RuleFactory
     */
    protected $ruleFactory;

    /**
     * @var \Magento\SalesRule\Model\Resource\Coupon\CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var \Magento\SalesRule\Model\Coupon\Massgenerator
     */
    protected $couponGenerator;

    /**
     * @var \Magento\SalesRule\Model\Spi\CouponResourceInterface
     */
    protected $resourceModel;

    /**
     * @param \Magento\SalesRule\Model\CouponFactory $couponFactory
     * @param \Magento\SalesRule\Model\RuleFactory $ruleFactory
     * @param \Magento\SalesRule\Model\Resource\Coupon\CollectionFactory $collectionFactory
     * @param \Magento\SalesRule\Model\Coupon\Massgenerator $couponGenerator
     * @param \Magento\SalesRule\Model\Spi\CouponResourceInterface $resourceModel
     */
    public function __construct(
        \Magento\SalesRule\Model\CouponFactory $couponFactory,
        \Magento\SalesRule\Model\RuleFactory $ruleFactory,
        \Magento\SalesRule\Model\Resource\Coupon\CollectionFactory $collectionFactory,
        \Magento\SalesRule\Model\Coupon\Massgenerator $couponGenerator,
        \Magento\SalesRule\Model\Spi\CouponResourceInterface $resourceModel
    ) {
        $this->couponFactory = $couponFactory;
        $this->ruleFactory = $ruleFactory;
        $this->collectionFactory = $collectionFactory;
        $this->couponGenerator = $couponGenerator;
        $this->resourceModel = $resourceModel;
    }

    /**
     * Generate coupon for a rule
     *
     * @param \Magento\SalesRule\Api\Data\CouponGenerationSpecInterface $couponSpec
     * @return string[]
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function generate(\Magento\SalesRule\Api\Data\CouponGenerationSpecInterface $couponSpec)
    {
        $data = $this->convertCouponSpec($couponSpec);
        if (!$this->couponGenerator->validateData($data)) {
            throw new \Magento\Framework\Exception\InputException();
        }

        try {
            $this->couponGenerator->setData($data);
            $rule = $this->ruleFactory->create()->load($this->couponGenerator->getRuleId());
            if (!$rule->getRuleId()) {
                throw \Magento\Framework\Exception\NoSuchEntityException::singleField(
                    \Magento\SalesRule\Model\Coupon::KEY_RULE_ID,
                    $this->couponGenerator->getRuleId()
                );
            }
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
     */
    protected function convertCouponSpec(\Magento\SalesRule\Api\Data\CouponGenerationSpecInterface $couponSpec)
    {
        $data = [];
        $data['rule_id'] = $couponSpec->getRuleId();
        $data['qty'] = $couponSpec->getQuantity();
        $data['format'] = $couponSpec->getFormat();
        $data['length'] = $couponSpec->getLength();
        $data['to_date'] = $couponSpec->getExpirationDate();
        $data['uses_per_coupon'] = $couponSpec->getUsagePerCoupon();
        $data['uses_per_customer'] = $couponSpec->getUsagePerCustomer();
        return $data;
    }

    /**
     * Delete coupon by coupon ids.
     *
     * @param int[] $ids
     * @param bool $ignoreInvalidIds
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteByIds(array $ids, $ignoreInvalidIds = true)
    {
        return $this->massDelete('coupon_id', $ids, $ignoreInvalidIds);
    }

    /**
     * Delete coupon by coupon codes.
     *
     * @param string[] codes
     * @param bool $ignoreInvalidCodes
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteByCodes(array $codes, $ignoreInvalidCodes = true)
    {
        return $this->massDelete('code', $codes, $ignoreInvalidCodes);
    }

    /**
     * Delete coupons by filter
     *
     * @param string $fieldName
     * @param string[] fieldValues
     * @param bool $ignoreInvalid
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
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
        try {
            /** @var \Magento\SalesRule\Model\Coupon $coupon */
            foreach ($couponsCollection->getItems() as $coupon) {
                $coupon->delete();
            }
            return true;
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('Error occurred when deleting coupons: %1.', $e->getMessage())
            );
        }
    }
}
