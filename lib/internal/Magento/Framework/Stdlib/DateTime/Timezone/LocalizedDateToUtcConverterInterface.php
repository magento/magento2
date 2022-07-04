<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Stdlib\DateTime\Timezone;

/*
 * Interface for converting localized date to UTC
 *
 * @api
 */
interface LocalizedDateToUtcConverterInterface
{
    /**
     * Convert localized date to UTC
     *
     * @param string $date
     * @return string
     */
    public function convertLocalizedDateToUtc($date);
}
