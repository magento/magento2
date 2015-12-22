<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Model;

use Magento\Payment\Gateway\ConfigInterface;

/**
 * Class VaultConfigMock
 */
class VaultConfigMock implements ConfigInterface
{
    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getValue($field, $storeId = null)
    {
        switch ($field) {
            case 'model':
                return VaultPaymentMock::class;
        }

        return null;
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setMethodCode($methodCode)
    {
        //
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setPathPattern($pathPattern)
    {
        //
    }
}
