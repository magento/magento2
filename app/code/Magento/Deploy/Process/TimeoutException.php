<?php
/**
 * Copyright 2020 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Deploy\Process;

/**
 * Exception is thrown if deploy process is finished due to timeout.
 */
class TimeoutException extends \RuntimeException
{
}
