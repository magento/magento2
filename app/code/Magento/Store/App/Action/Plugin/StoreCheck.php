<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Store\App\Action\Plugin;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\Exception\State\InitException;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Plugin verify Store on before Execute on ActionInterface
 */
class StoreCheck
{
    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        StoreManagerInterface $storeManager
    ) {
        $this->_storeManager = $storeManager;
    }

    /**
     * Verify before execute
     *
     * @param ActionInterface $subject
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @throws \Magento\Framework\Exception\State\InitException
     */
    public function beforeExecute(ActionInterface $subject)
    {
        if (!$this->_storeManager->getStore()->isActive()) {
            throw new InitException(
                __('Current store is not active.')
            );
        }
    }
}
