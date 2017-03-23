<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Payment\Gateway\Config;

use Magento\Payment\Gateway\ConfigInterface;
use Magento\Payment\Gateway\Helper\SubjectReader;

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
     * @param array $subject
     * @param int|null $storeId
     *
     * @return mixed
     */
    public function handle(array $subject, $storeId = null)
    {
        return $this->configInterface->getValue(SubjectReader::readField($subject), $storeId);
    }
}
