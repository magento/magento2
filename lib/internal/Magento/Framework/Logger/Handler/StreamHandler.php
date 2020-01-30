<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Framework\Logger\Handler;

use Monolog\Formatter\FormatterInterface;
use Monolog\Logger;

/**
 * Stream handler with configurable formatter
 */
class StreamHandler extends \Monolog\Handler\StreamHandler
{
    /**
     * StreamHandler constructor.
     * @param resource|string $stream
     * @param int $level
     * @param bool $bubble
     * @param int|null $filePermission
     * @param bool $useLocking
     * @param FormatterInterface|null $formatter
     * @throws \Exception
     */
    public function __construct(
        $stream,
        $level = Logger::DEBUG,
        $bubble = true,
        $filePermission = null,
        $useLocking = false,
        FormatterInterface $formatter = null
    ) {
        parent::__construct($stream, $level, $bubble, $filePermission, $useLocking);
        $this->formatter = $formatter;
    }
}
