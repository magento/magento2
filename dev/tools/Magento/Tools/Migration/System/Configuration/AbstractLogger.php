<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Tools\Migration\System\Configuration;

/**
 * System configuration migration logger
 */
abstract class AbstractLogger
{
    const FILE_KEY_VALID = 'valid';

    const FILE_KEY_INVALID = 'invalid';

    /**
     * List of logs
     *
     * @var array
     */
    protected $_logs = [self::FILE_KEY_VALID => [], self::FILE_KEY_INVALID => []];

    /**
     * Add log data
     *
     * @param string $fileName
     * @param string $type
     * @return \Magento\Tools\Migration\System\Configuration\AbstractLogger
     */
    public function add($fileName, $type)
    {
        $this->_logs[$type][] = $fileName;
        return $this;
    }

    /**
     * Convert logger object to string
     *
     * @return string
     */
    public function __toString()
    {
        $result = [];
        $totalCount = 0;
        foreach ($this->_logs as $type => $data) {
            $countElements = count($data);
            $totalCount += $countElements;
            $total[] = $type . ': ' . $countElements;

            if (!$countElements) {
                continue;
            }

            $result[] = '------------------------------';
            $result[] = $type . ':';
            foreach ($data as $fileName) {
                $result[] = $fileName;
            }
        }

        $total[] = 'Total: ' . $totalCount;
        $result = array_merge($total, $result);
        return implode(PHP_EOL, $result);
    }

    /**
     * Generate report
     *
     * @return void
     */
    abstract public function report();
}
