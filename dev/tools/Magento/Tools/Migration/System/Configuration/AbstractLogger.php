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
    protected $_logs = array(self::FILE_KEY_VALID => array(), self::FILE_KEY_INVALID => array());

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
        $result = array();
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
