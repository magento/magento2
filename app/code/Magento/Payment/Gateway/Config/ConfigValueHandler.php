<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Gateway\Config;

use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;

/**
 * Default implementation of config value handler.
 *
 * This class is designed to be injected into other classes. Inheritance in not recommended.
 *
 * @api
 * @since 2.0.0
 */
class ConfigValueHandler implements ValueHandlerInterface
{
    /**
     * @var \Magento\Payment\Gateway\ConfigInterface
     * @since 2.0.0
     */
    private $configInterface;

    /**
     * @param \Magento\Payment\Gateway\ConfigInterface $configInterface
     * @since 2.0.0
     */
    public function __construct(
        ConfigInterface $configInterface
    ) {
        $this->configInterface = $configInterface;
    }

    /**
     * Retrieve method configured value
     *
     * @param array $subject
     * @param int|null $storeId
     *
     * @return mixed
     * @since 2.0.0
     */
    public function handle(array $subject, $storeId = null)
    {
        return $this->configInterface->getValue(SubjectReader::readField($subject), $storeId);
    }
}
