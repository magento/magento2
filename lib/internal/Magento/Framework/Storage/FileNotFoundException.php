<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Storage;

/**
 * Exception: FileNotFoundException
 *
 * Exception to be thrown when the a requested file does not exists
 */
class FileNotFoundException extends \RuntimeException
{
}
