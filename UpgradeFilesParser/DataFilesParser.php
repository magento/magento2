<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * A custom "Import" adapter for Magento_ImportExport module that allows generating arbitrary data rows
 */
namespace Magento\Setup\Model;

class DataFilesParser
{
    private $argumentPattern = '/(.*function.*[^\(]+\()([^\)]+)/';

    private $keyPatterns = [
        '/class\s(\w+).*/',
        '/function\s([^\s]+)\(.*/',
        '/version_compare.*(\d+\.\d+\.\d+)/',
    ];

    private $keyPatternsToGoAhead = [
        "/{\n/"
    ];

    private $ignoreCases = ["/.*}.*\\{.*/"];

    private $currentKey = null;

    private $ignoreCloseBraсket = 0;

    private $writeToArgument = false;

    public function parse(&$result, $fileDescriptor)
    {
        $lineData = fgets($fileDescriptor);

        if ($lineData === false) {
            return $this;
        }

        if ($this->shouldGoOver($lineData)) {
            if ($this->ignoreCloseBraсket === 0) {
                return $this;
            } elseif ($this->ignoreCloseBraсket > 0) {
                $this->ignoreCloseBraсket--;
            }
        }

        $this->registerCurrentKey($lineData);

        if ($this->shouldStartArgument($lineData)) {
            $this->writeToArgument = true;
        }

        if ($this->writeToArgument) {
            $arguments = isset($result[$this->currentKey]['arguments']) ?
                $result[$this->currentKey]['arguments'] : '';
            $result[$this->currentKey]['arguments'] = $arguments . $lineData;
        }

        if ($this->shouldStopArgument($lineData)) {
            $result[$this->currentKey]['arguments'] = $this->processArguments(
                $result[$this->currentKey]['arguments']
            );
            $this->writeToArgument = false;
        }

        if ($this->shouldGoAhead($lineData)) {
            $key = $this->getAndFlushCurrentKey();

            if (!$key) {
                $this->ignoreCloseBraсket++;
                $result[] = $lineData;
            } else {
                $this->parse($_r, $fileDescriptor);
                $result[$key]['data'] = $_r;
            }
        } else {
            $result[] = $lineData;
        }

        return $this->parse($result, $fileDescriptor);
    }

    private function processArguments($arguments)
    {
        $arguments = preg_replace($this->argumentPattern, "$2", $arguments);
        $arguments = str_replace("(", "", $arguments);
        $arguments = str_replace(")", "", $arguments);
        $arguments = str_replace("}", "", $arguments);
        $arguments = str_replace("{", "", $arguments);
        return explode(",", $arguments);
    }

    private function shouldStartArgument($line)
    {
        return preg_match($this->argumentPattern, $line) && !$this->isCallable($line);
    }

    private function isCallable($line)
    {
        return preg_match('/function\s\(.*\)/', $line);
    }

    private function shouldStopArgument($line)
    {
        return $this->writeToArgument && preg_match("/\\)/", $line);
    }

    private function getAndFlushCurrentKey()
    {
        $_currentKey = $this->currentKey;
        $this->currentKey = null;
        return $_currentKey;
    }

    private function registerCurrentKey($lineData)
    {
        foreach ($this->keyPatterns as $keyPattern) {
            if (preg_match($keyPattern, $lineData, $matches)) {
                if ($this->currentKey && $this->currentKey !== $matches[1]) {
                    throw new \Exception("Local current key is already defined");
                }

                $this->currentKey = $matches[1];
            }
        }
    }

    private function isIgnoreCase($lineData)
    {
        foreach ($this->ignoreCases as $case) {
            if (preg_match($case, $lineData)) {
                return true;
            }
        }

        return false;
    }

    private function shouldGoOver($lineData)
    {
        return preg_match("/\\s}\n/", $lineData) && !$this->isIgnoreCase($lineData);
    }

    private function shouldGoAhead($lineData)
    {
        foreach ($this->keyPatternsToGoAhead as $pattern) {
            if (preg_match($pattern, $lineData)) {
                if ($this->isIgnoreCase($lineData)) continue;
                return true;
            }
        }

        return false;
    }
}
