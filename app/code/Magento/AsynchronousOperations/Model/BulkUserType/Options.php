<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AsynchronousOperations\Model\BulkUserType;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Framework\Data\OptionSourceInterface;

class Options implements OptionSourceInterface
{
    /**
     * @inheritDoc
     */
    public function toOptionArray(): array
    {
        return [
            [
                'value' => UserContextInterface::USER_TYPE_ADMIN,
                'label' => __('Admin user')
            ],
            [
                'value' => UserContextInterface::USER_TYPE_INTEGRATION,
                'label' => __('Integration')
            ]
        ];
    }
}
