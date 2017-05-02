<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\ImportExport\Test\Unit\Model\Import\Source\FileParser;

use Magento\Framework\Filesystem\File\ReadInterface;

class FakeFile implements ReadInterface
{
    private $lines;
    private $pointer;
    private $isOpen;

    public function __construct(array $lines)
    {
        $this->lines = $lines;
        $this->pointer = 0;
        $this->isOpen = true;
    }

    public function isOpen()
    {
        return $this->isOpen;
    }

    public function readCsv($length = 0, $delimiter = ',', $enclosure = '"', $escape = '\\')
    {
        if ($this->isEndOfFile()) {
            return false;
        }

        return str_getcsv($this->readFileLine($length), $delimiter, $enclosure, $escape);
    }

    private function readFileLine($length)
    {
        return $this->truncateLineToLength($this->lines[$this->pointer++], $length);
    }

    private function isEndOfFile()
    {
        return !isset($this->lines[$this->pointer]);
    }

    private function truncateLineToLength($line, $length)
    {
        if ($length > 0 && strlen($line) > $length) {
            $line = substr($line, 0, $length);
        }
        return $line;
    }
    public function close()
    {
        $this->isOpen = false;
    }

    public function read($length)
    {
        return '';
    }

    public function readLine($length, $ending = null)
    {
        return $this->readFileLine($length);
    }

    public function tell()
    {
        return $this->pointer;
    }

    public function seek($length, $whence = SEEK_SET)
    {
        $this->pointer = $length;
    }

    public function eof()
    {
        return $this->isEndOfFile();
    }

    public function stat()
    {
        return [];
    }
}
