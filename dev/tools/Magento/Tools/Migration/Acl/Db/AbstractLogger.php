<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright  Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
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
    protected $_logs = array();

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
        $output = array('Mapped items' => array(), 'Not mapped items' => array(), 'Items in actual format' => array());
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
