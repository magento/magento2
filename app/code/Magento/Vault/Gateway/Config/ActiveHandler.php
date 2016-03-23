<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Vault\Gateway\Config;

use Magento\Payment\Gateway\ConfigInterface;
use Magento\Vault\Model\Adminhtml\Source\VaultProvidersMap;
use Magento\Payment\Gateway\Config\ValueHandlerInterface;

/**
 * Class ActiveHandler
 */
class ActiveHandler implements ValueHandlerInterface
{
    /**
     * @var ConfigInterface
     */
    private $config;

    /**
     * @param ConfigInterface $config
     */
    public function __construct(ConfigInterface $config)
    {
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    public function handle(array $subject, $storeId = null)
    {
        $vaultPaymentCode = $this->config->getValue(VaultProvidersMap::VALUE_CODE, $storeId);

        return (int) ((int)$this->config->getValue('active', $storeId) === 1
            && $vaultPaymentCode
            && $vaultPaymentCode !== VaultProvidersMap::EMPTY_VALUE);
    }
}
