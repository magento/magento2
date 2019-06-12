<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Payment\Gateway\ErrorMapper;

use Magento\Framework\Config\DataInterface;

/**
 * Stub implementation of DataInterface which is used by default for ErrorMessageMapper, because
 * each payment method should provide own mapping data source.
 */
class NullMappingData implements DataInterface
{
    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function get($path = null, $default = null)
    {
        return null;
    }

    /**
     * @inheritdoc
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function merge(array $config)
    {
    }
}
