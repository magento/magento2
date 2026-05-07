<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Usps\Model\Source;

class RestMethod extends Generic
{
    /**
     * Carrier code
     *
     * @var string
     */
    public $code = 'rest_method';
}
