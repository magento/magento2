<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\GraphQl\Controller\HttpHeaderProcessor;

use Magento\Framework\GraphQl\HttpHeaderProcessorInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;

/**
 * Process the "Store" header entry
 */
class StoreProcessor implements HttpHeaderProcessorInterface
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * StoreProcessor constructor.
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(StoreManagerInterface $storeManager)
    {
        $this->storeManager = $storeManager;
    }

    /**
     * Handle the value of the store and set the scope
     *
     * {@inheritDoc}
     * @throws NoSuchEntityException
     */
    public function processHeaderValue($headerValue)
    {
        if ($headerValue) {
            $storeCode = ltrim(rtrim($headerValue));
            $stores = $this->storeManager->getStores(false, true);
            if (isset($stores[$storeCode])) {
                $this->storeManager->setCurrentStore($storeCode);
            } elseif (strtolower($storeCode) !== 'default') {
                throw new GraphQlInputException(
                    new \Magento\Framework\Phrase('Store code %1 does not exist', [$storeCode])
                );
            }
        }
    }
}
