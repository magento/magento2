<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Gateway\Request;

use Magento\AuthorizenetAcceptjs\Gateway\Config;
use Magento\AuthorizenetAcceptjs\Gateway\SubjectReader;
use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Adds the custom settings to the request
 *
 * @deprecated 100.3.3 Starting from Magento 2.3.4 Authorize.net payment method core integration is deprecated in favor of
 * official payment integration available on the marketplace
 */
class CustomSettingsBuilder implements BuilderInterface
{
    /**
     * @var SubjectReader
     */
    private $subjectReader;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param SubjectReader $subjectReader
     * @param Config $config
     */
    public function __construct(SubjectReader $subjectReader, Config $config)
    {
        $this->subjectReader = $subjectReader;
        $this->config = $config;
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject): array
    {
        $result = [];

        if ($this->config->shouldEmailCustomer($this->subjectReader->readStoreId($buildSubject))) {
            $result['transactionRequest'] = [
                'transactionSettings' => [
                    'setting' => [
                        [
                            'settingName' => 'emailCustomer',
                            'settingValue' => 'true'
                        ]
                    ]
                ]
            ];
        }

        return $result;
    }
}
