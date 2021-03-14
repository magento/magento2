<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Interception\Code\Generator;

/**
 * Interface InterceptorInterface
 */
interface InterceptorInterface
{
    /**
     * Generation template method
     *
     * @return mixed
     */
    public function generate();
}
