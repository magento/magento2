<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\AuthorizenetAcceptjs\Gateway\Request;

use Magento\AuthorizenetAcceptjs\Model\PassthroughDataObject;
use Magento\Payment\Gateway\Request\BuilderInterface;

/**
 * Adds data to the request that can be used in the response
 *
 * @deprecated 100.3.3 Starting from Magento 2.3.4 Authorize.net payment method core integration is deprecated in favor of
 * official payment integration available on the marketplace
 */
class PassthroughDataBuilder implements BuilderInterface
{
    /**
     * @var PassthroughDataObject
     */
    private $passthroughData;

    /**
     * @param PassthroughDataObject $passthroughData
     */
    public function __construct(PassthroughDataObject $passthroughData)
    {
        $this->passthroughData = $passthroughData;
    }

    /**
     * @inheritdoc
     */
    public function build(array $buildSubject): array
    {
        $fields = [];

        foreach ($this->passthroughData->getData() as $key => $value) {
            $fields[] = [
                'name' => $key,
                'value' => $value
            ];
        }

        if (!empty($fields)) {
            return [
                'transactionRequest' => [
                    'userFields' => [
                        'userField' => $fields
                    ]
                ]
            ];
        }

        return [];
    }
}
