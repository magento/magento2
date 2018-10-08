<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Stdlib\DateTime\Timezone;

interface LocalizedDateToUtcConverterInterface
{
    /**
     * @param string $data
     * @return string
     */
    public function convertLocalizedDateToUtc($date);
}