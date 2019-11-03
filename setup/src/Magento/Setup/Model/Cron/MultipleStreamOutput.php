<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Cron;

use Symfony\Component\Console\Formatter\OutputFormatterInterface;
use Symfony\Component\Console\Output\Output;

/**
 * Class to allow output to multiple file streams
 */
class MultipleStreamOutput extends Output
{
    /**
     * @var array
     */
    private $streams;

    /**
     * Constructor
     *
     * @param array $streams
     * @param bool|int $verbosity
     * @param bool $decorated
     * @param OutputFormatterInterface $formatter
     */
    public function __construct(
        array $streams,
        $verbosity = self::VERBOSITY_NORMAL,
        $decorated = false,
        OutputFormatterInterface $formatter = null
    ) {
        foreach ($streams as $stream) {
            if (!is_resource($stream) || 'stream' !== get_resource_type($stream)) {
                throw new \InvalidArgumentException('The StreamOutput class needs a stream as its first argument.');
            }
        }
        $this->streams = $streams;
        parent::__construct($verbosity, $decorated, $formatter);
    }

    /**
     * {@inheritdoc}
     */
    protected function doWrite($message, $newline)
    {
        foreach ($this->streams as $stream) {
            if (false === @fwrite($stream, $message . ($newline ? PHP_EOL : ''))) {
                // should never happen
                throw new \RuntimeException('Unable to write output.');
            }

            fflush($stream);
        }
    }
}
