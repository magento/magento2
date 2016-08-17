<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
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
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws \Magento\Framework\Exception\State\InitException
     */
    public function beforeDispatch(\Magento\Framework\App\Action\AbstractAction $subject)
    {
        if (!$this->_storeManager->getStore()->isActive()) {
            throw new \Magento\Framework\Exception\State\InitException(
                __('Current store is not active.')
            );
        }
    }
}
