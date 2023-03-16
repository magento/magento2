<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Model\Service;

use Exception;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\SalesRule\Api\CouponManagementInterface;
use Magento\SalesRule\Api\CouponRepositoryInterface;
use Magento\SalesRule\Api\Data\CouponGenerationSpecInterface;
use Magento\SalesRule\Api\Data\CouponMassDeleteResultInterface;
use Magento\SalesRule\Api\Data\CouponMassDeleteResultInterfaceFactory;
use Magento\SalesRule\Model\Coupon;
use Magento\SalesRule\Model\Coupon\Massgenerator as CouponMassgenerator;
use Magento\SalesRule\Model\CouponFactory;
use Magento\SalesRule\Model\ResourceModel\Coupon\CollectionFactory;
use Magento\SalesRule\Model\Rule;
use Magento\SalesRule\Model\RuleFactory;
use Magento\SalesRule\Model\Spi\CouponResourceInterface;

/**
 * Coupon management service class
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CouponManagementService implements CouponManagementInterface
{
    /**
     * @param CouponFactory $couponFactory @deprecated 101.1.2
     * @param RuleFactory $ruleFactory
     * @param CollectionFactory $collectionFactory @deprecated 101.1.2
     * @param CouponMassgenerator $couponGenerator
     * @param CouponResourceInterface $resourceModel @deprecated 101.1.2
     * @param CouponMassDeleteResultInterfaceFactory $couponMassDeleteResultFactory
     * @param SearchCriteriaBuilder|null $criteriaBuilder
     * @param CouponRepositoryInterface|null $repository
     */
    public function __construct(
        protected readonly CouponFactory $couponFactory,
        protected readonly RuleFactory $ruleFactory,
        protected readonly CollectionFactory $collectionFactory,
        protected readonly CouponMassgenerator $couponGenerator,
        protected readonly CouponResourceInterface $resourceModel,
        protected readonly CouponMassDeleteResultInterfaceFactory $couponMassDeleteResultFactory,
        private ?SearchCriteriaBuilder $criteriaBuilder = null,
        private ?CouponRepositoryInterface $repository = null
    ) {
        $this->criteriaBuilder = $criteriaBuilder ?? ObjectManager::getInstance()->get(SearchCriteriaBuilder::class);
        $this->repository = $repository ?? ObjectManager::getInstance()->get(CouponRepositoryInterface::class);
    }

    /**
     * Generate coupon for a rule
     *
     * @param CouponGenerationSpecInterface $couponSpec
     * @return string[]
     * @throws InputException
     * @throws LocalizedException
     */
    public function generate(CouponGenerationSpecInterface $couponSpec)
    {
        $data = $this->convertCouponSpec($couponSpec);
        if (!$this->couponGenerator->validateData($data)) {
            throw new InputException();
        }

        try {
            $rule = $this->ruleFactory->create()->load($couponSpec->getRuleId());
            if (!$rule->getRuleId()) {
                throw NoSuchEntityException::singleField(
                    Coupon::KEY_RULE_ID,
                    $couponSpec->getRuleId()
                );
            }
            if (!$rule->getUseAutoGeneration()
                && $rule->getCouponType() != Rule::COUPON_TYPE_AUTO
            ) {
                throw new LocalizedException(
                    __('Specified rule does not allow automatic coupon generation')
                );
            }

            $this->couponGenerator->setData($data);
            $this->couponGenerator->setData('to_date', $rule->getToDate());
            $this->couponGenerator->setData('uses_per_coupon', $rule->getUsesPerCoupon());
            $this->couponGenerator->setData('usage_per_customer', $rule->getUsesPerCustomer());

            $this->couponGenerator->generatePool();
            return $this->couponGenerator->getGeneratedCodes();
        } catch (Exception $e) {
            throw new LocalizedException(
                __('Error occurred when generating coupons: %1', $e->getMessage())
            );
        }
    }

    /**
     * Convert CouponGenerationSpecInterface to data array expected by Massgenerator
     *
     * @param CouponGenerationSpecInterface $couponSpec
     * @return array
     */
    protected function convertCouponSpec(CouponGenerationSpecInterface $couponSpec)
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
     * @return CouponMassDeleteResultInterface
     * @throws LocalizedException
     */
    public function deleteByIds(array $ids, $ignoreInvalidCoupons = true)
    {
        return $this->massDelete('coupon_id', $ids, $ignoreInvalidCoupons);
    }

    /**
     * Delete coupon by coupon codes.
     *
     * @param string[] $codes
     * @param bool $ignoreInvalidCoupons
     * @return CouponMassDeleteResultInterface
     * @throws LocalizedException
     */
    public function deleteByCodes(array $codes, $ignoreInvalidCoupons = true)
    {
        return $this->massDelete('code', $codes, $ignoreInvalidCoupons);
    }

    /**
     * Delete coupons by filter
     *
     * @param string $fieldName
     * @param string[] $fieldValues
     * @param bool $ignoreInvalid
     * @return CouponMassDeleteResultInterface
     * @throws LocalizedException
     */
    protected function massDelete($fieldName, array $fieldValues, $ignoreInvalid)
    {
        $this->criteriaBuilder->addFilter($fieldName, $fieldValues, 'in');
        $couponsCollection = $this->repository->getList($this->criteriaBuilder->create());

        if (!$ignoreInvalid) {
            if ($couponsCollection->getTotalCount() != count($fieldValues)) {
                throw new LocalizedException(__('Some coupons are invalid.'));
            }
        }

        $results = $this->couponMassDeleteResultFactory->create();
        $failedItems = [];
        $fieldValues = array_flip($fieldValues);
        foreach ($couponsCollection->getItems() as $coupon) {
            $couponValue = ($fieldName == 'code') ? $coupon->getCode() : $coupon->getCouponId();
            try {
                $this->repository->deleteById($coupon->getCouponId());
            } catch (Exception $e) {
                $failedItems[] = $couponValue;
            }
            unset($fieldValues[$couponValue]);
        }
        $results->setFailedItems($failedItems);
        $results->setMissingItems(array_flip($fieldValues));
        return $results;
    }
}
