<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\Di\App\Task;

/**
 * Class \Magento\Setup\Module\Di\App\Task\OperationException
 *
 * @since 2.0.0
 */
class OperationException extends \Exception
{
    /**
     * Unavailable operation code
     */
    const UNAVAILABLE_OPERATION = 1;
}
