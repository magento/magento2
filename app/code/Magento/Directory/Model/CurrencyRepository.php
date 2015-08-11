<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Directory\Model;

/**
 * Currency class
 *
 */
class CurrencyRepository implements \Magento\Directory\Api\CurrencyRepositoryInterface
{
    /**
    * @var \Magento\Store\Model\StoreManagerInterface
    */
    protected $storeManager;
    
    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;
    }
    /**
     * {@inheritdoc}
     */
    public function getCurrency($storeId = null)
    {
    }
}
