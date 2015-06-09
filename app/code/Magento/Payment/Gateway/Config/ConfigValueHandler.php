<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Gateway\Config;

use Magento\Payment\Gateway\ConfigInterface;

class ConfigValueHandler implements ValueHandlerInterface
{
    /**
     * @var \Magento\Payment\Gateway\ConfigInterface
     */
    private $configInterface;

    /**
     * @param \Magento\Payment\Gateway\ConfigInterface $configInterface
     */
    public function __construct(
        ConfigInterface $configInterface
    ) {
        $this->configInterface = $configInterface;
    }

    /**
     * Retrieve method configured value
     *
     * @param string $field
     * @param int|null $storeId
     *
     * @return mixed
     */
    public function handle($field, $storeId = null)
    {
        return $this->configInterface->getValue($field, $storeId);
    }
}
