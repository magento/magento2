<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\App\Action\Plugin;

use Magento\Framework\App\ActionInterface;

class StoreCheck
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_storeManager = $storeManager;
    }

    /**
     * @param ActionInterface $subject
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws \Magento\Framework\Exception\State\InitException
     */
    public function beforeExecute(ActionInterface $subject)
    {
        if (! $this->_storeManager->getStore()->isActive()) {
            throw new \Magento\Framework\Exception\State\InitException(
                __('Current store is not active.')
            );
        }
    }
}
