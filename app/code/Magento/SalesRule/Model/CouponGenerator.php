<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Model;

/**
 * Allows to generate a pool of coupon codes.
 *
 * Generated coupon code - auto generated string, which is used on checkout in order to get
 * discount (fixed or in percents) on whole customer shopping cart or on items in this shopping cart.
 * Class was added due to Backward Compatibility and is used as proxy to:
 * @see \Magento\SalesRule\Model\Service\CouponManagementService
 * @since 2.2.0
 */
class CouponGenerator
{
    /**
     * Map keys in old and new services
     *
     * Controller was used as old service
     * @see \Magento\SalesRule\Controller\Adminhtml\Promo\Quote\Generate
     *  - key = key in new service
     *  - value = key in old service
     *
     * @var array
     * @since 2.2.0
     */
    private $keyMap = [
        'quantity' => 'qty'
    ];

    /**
     * @var Service\CouponManagementService
     * @since 2.2.0
     */
    private $couponManagementService;

    /**
     * @var \Magento\SalesRule\Api\Data\CouponGenerationSpecInterfaceFactory
     * @since 2.2.0
     */
    private $generationSpecFactory;

    /**
     * All objects should be injected through constructor, because we need to have working service already
     * after it initializing
     *
     * @param Service\CouponManagementService $couponManagementService
     * @param \Magento\SalesRule\Api\Data\CouponGenerationSpecInterfaceFactory $generationSpecFactory
     * @since 2.2.0
     */
    public function __construct(
        \Magento\SalesRule\Model\Service\CouponManagementService $couponManagementService,
        \Magento\SalesRule\Api\Data\CouponGenerationSpecInterfaceFactory $generationSpecFactory
    ) {
        $this->couponManagementService = $couponManagementService;
        $this->generationSpecFactory = $generationSpecFactory;
    }

    /**
     * Generate a pool of generated coupon codes
     *
     * This method is used as proxy, due to high coupling in constructor
     * @see \Magento\SalesRule\Controller\Adminhtml\Promo\Quote\Generate
     * In order to generate valid coupon codes, we need to initialize DTO object and run service.
     * @see \Magento\SalesRule\Api\Data\CouponGenerationSpecInterface -> DTO object
     *
     * @param array $parameters
     * @return string[]
     * @since 2.2.0
     */
    public function generateCodes(array $parameters)
    {
        $couponSpecData = $this->convertCouponSpecData($parameters);
        $couponSpec = $this->generationSpecFactory->create(['data' => $couponSpecData]);
        return $this->couponManagementService->generate($couponSpec);
    }

    /**
     * We should map old values to new one
     * We need to do this, as new service with another key names was added
     *
     * @param array $data
     * @return array
     * @since 2.2.0
     */
    private function convertCouponSpecData(array $data)
    {
        foreach ($this->keyMap as $mapKey => $mapValue) {
            $data[$mapKey] = isset($data[$mapValue]) ? $data[$mapValue] : null;
        }

        return $data;
    }
}
