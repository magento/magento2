<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * DB migration logger
 */
namespace Magento\Tools\Migration\Acl\Db;

abstract class AbstractLogger
{
    /**
     * List of logs
     *
     * @var array
     */
    protected $_logs = [];

    /**
     * Convert list of logs to nice text block
     *
     * @param string $message block header text
     * @param array $list list of logs
     * @return string
     */
    protected function _logsListToString($message, $list)
    {
        $outputString = $message . ':' . PHP_EOL;
        $outputString .= implode(PHP_EOL, $list);
        $outputString .= PHP_EOL . str_repeat('-', 30) . PHP_EOL;

        return $outputString;
    }

    /**
     * Add log data
     *
     * @param string $oldKey
     * @param string $newKey
     * @param int|null $updateResult
     * @return \Magento\Tools\Migration\Acl\Db\AbstractLogger
     */
    public function add($oldKey, $newKey, $updateResult)
    {
        if (empty($oldKey)) {
            $oldKey = $newKey;
        }
        $this->_logs[$oldKey]['newKey'] = $newKey;
        $this->_logs[$oldKey]['updateResult'] = $updateResult;
        return $this;
    }

    /**
     * Convert logger object to string
     *
     * @return string
     */
    public function __toString()
    {
        $output = ['Mapped items' => [], 'Not mapped items' => [], 'Items in actual format' => []];
        foreach ($this->_logs as $oldKey => $data) {
            $newKey = $data['newKey'];
            $countItems = $data['updateResult'];

            if ($oldKey == $newKey) {
                $output['Items in actual format'][$oldKey] = $oldKey;
            } elseif (empty($newKey)) {
                $output['Not mapped items'][$oldKey] = $oldKey;
            } else {
                $output['Mapped items'][$oldKey] = $oldKey .
                    ' => ' .
                    $newKey .
                    ' :: Count updated rules: ' .
                    $countItems;
            }
        }

        $generalBlock = $detailsBlock = '';
        foreach ($output as $key => $data) {
            $generalBlock .= $key . ' count: ' . count($data) . PHP_EOL;
            if (count($data)) {
                $detailsBlock .= $this->_logsListToString($key, $data);
            }
        }
        return $generalBlock . str_repeat('-', 30) . PHP_EOL . $detailsBlock;
    }

    /**
     * Generate report
     *
     * @abstract
     * @return mixed
     */
    abstract public function report();
}
