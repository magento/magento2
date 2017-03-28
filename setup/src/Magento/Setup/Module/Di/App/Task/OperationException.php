<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\Di\App\Task;

class OperationException extends \Exception
{
    /**
     * Unavailable operation code
     */
    const UNAVAILABLE_OPERATION = 1;
}
