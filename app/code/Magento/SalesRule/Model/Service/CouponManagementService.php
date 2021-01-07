<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Model\Service;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\SalesRule\Api\CouponRepositoryInterface;

/**
 * Coupon management service class
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CouponManagementService implements \Magento\SalesRule\Api\CouponManagementInterface
{
    /**
     * @var \Magento\SalesRule\Model\CouponFactory
     * @deprecated 101.1.2
     */
    protected $couponFactory;

    /**
     * @var \Magento\SalesRule\Model\RuleFactory
     */
    protected $ruleFactory;

    /**
     * @var \Magento\SalesRule\Model\ResourceModel\Coupon\CollectionFactory
     * @deprecated 101.1.2
     */
    protected $collectionFactory;

    /**
     * @var \Magento\SalesRule\Model\Coupon\Massgenerator
     */
    protected $couponGenerator;

    /**
     * @var \Magento\SalesRule\Model\Spi\CouponResourceInterface
     * @deprecated 101.1.2
     */
    protected $resourceModel;

    /**
     * var \Magento\SalesRule\Api\Data\CouponMassDeleteResultInterfaceFactory
     */
    protected $couponMassDeleteResultFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    private $criteriaBuilder;

    /**
     * @var CouponRepositoryInterface
     */
    private $repository;

    /**
     * @param \Magento\SalesRule\Model\CouponFactory $couponFactory
     * @param \Magento\SalesRule\Model\RuleFactory $ruleFactory
     * @param \Magento\SalesRule\Model\ResourceModel\Coupon\CollectionFactory $collectionFactory
     * @param \Magento\SalesRule\Model\Coupon\Massgenerator $couponGenerator
     * @param \Magento\SalesRule\Model\Spi\CouponResourceInterface $resourceModel
     * @param \Magento\SalesRule\Api\Data\CouponMassDeleteResultInterfaceFactory $couponMassDeleteResultFactory
     * @param SearchCriteriaBuilder|null $criteriaBuilder
     * @param CouponRepositoryInterface|null $repository
     */
    public function __construct(
        \Magento\SalesRule\Model\CouponFactory $couponFactory,
        \Magento\SalesRule\Model\RuleFactory $ruleFactory,
        \Magento\SalesRule\Model\ResourceModel\Coupon\CollectionFactory $collectionFactory,
        \Magento\SalesRule\Model\Coupon\Massgenerator $couponGenerator,
        \Magento\SalesRule\Model\Spi\CouponResourceInterface $resourceModel,
        \Magento\SalesRule\Api\Data\CouponMassDeleteResultInterfaceFactory $couponMassDeleteResultFactory,
        ?SearchCriteriaBuilder $criteriaBuilder = null,
        ?CouponRepositoryInterface $repository = null
    ) {
        $this->couponFactory = $couponFactory;
        $this->ruleFactory = $ruleFactory;
        $this->collectionFactory = $collectionFactory;
        $this->couponGenerator = $couponGenerator;
        $this->resourceModel = $resourceModel;
        $this->couponMassDeleteResultFactory = $couponMassDeleteResultFactory;
        $this->criteriaBuilder = $criteriaBuilder ?? ObjectManager::getInstance()->get(SearchCriteriaBuilder::class);
        $this->repository = $repository ?? ObjectManager::getInstance()->get(CouponRepositoryInterface::class);
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
            $rule = $this->ruleFactory->create()->load($couponSpec->getRuleId());
            if (!$rule->getRuleId()) {
                throw NoSuchEntityException::singleField(
                    \Magento\SalesRule\Model\Coupon::KEY_RULE_ID,
                    $couponSpec->getRuleId()
                );
            }
            if (!$rule->getUseAutoGeneration()
                && $rule->getCouponType() != \Magento\SalesRule\Model\Rule::COUPON_TYPE_AUTO
            ) {
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
     * @return \Magento\SalesRule\Api\Data\CouponMassDeleteResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
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
     * @return \Magento\SalesRule\Api\Data\CouponMassDeleteResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function massDelete($fieldName, array $fieldValues, $ignoreInvalid)
    {
        $this->criteriaBuilder->addFilter($fieldName, $fieldValues, 'in');
        $couponsCollection = $this->repository->getList($this->criteriaBuilder->create());

        if (!$ignoreInvalid) {
            if ($couponsCollection->getTotalCount() != count($fieldValues)) {
                throw new \Magento\Framework\Exception\LocalizedException(__('Some coupons are invalid.'));
            }
        }

        $results = $this->couponMassDeleteResultFactory->create();
        $failedItems = [];
        $fieldValues = array_flip($fieldValues);
        foreach ($couponsCollection->getItems() as $coupon) {
            $couponValue = ($fieldName == 'code') ? $coupon->getCode() : $coupon->getCouponId();
            try {
                $this->repository->deleteById($coupon->getCouponId());
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
