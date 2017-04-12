<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Store\App\Action\Plugin;

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
     * @param \Magento\Framework\App\Action\AbstractAction $subject
     * @param \Magento\Framework\App\RequestInterface $request
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws \Magento\Framework\Exception\State\InitException
     */
    public function beforeDispatch(
        \Magento\Framework\App\Action\AbstractAction $subject,
        \Magento\Framework\App\RequestInterface $request
    ) {
        if (!$this->_storeManager->getStore()->isActive()) {
            throw new \Magento\Framework\Exception\State\InitException(
                __('Current store is not active.')
            );
        }
    }
}
