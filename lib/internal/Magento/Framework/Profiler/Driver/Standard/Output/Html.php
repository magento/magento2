<?php
/**
 * Class that represents profiler output in HTML format
 *
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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\Profiler\Driver\Standard\Output;

use Magento\Framework\Profiler;
use Magento\Framework\Profiler\Driver\Standard\AbstractOutput;
use Magento\Framework\Profiler\Driver\Standard\Stat;

class Html extends AbstractOutput
{
    /**
     * Display profiling results
     *
     * @param Stat $stat
     * @return void
     */
    public function display(Stat $stat)
    {
        $out = array();
        $out[] = '<table border="1" cellspacing="0" cellpadding="2">';
        $out[] = '<caption>' . $this->_renderCaption() . '</caption>';
        $out[] = '<tr>';
        foreach (array_keys($this->_columns) as $columnLabel) {
            $out[] = '<th>' . $columnLabel . '</th>';
        }
        $out[] = '</tr>';
        foreach ($this->_getTimerIds($stat) as $timerId) {
            $out[] = '<tr>';
            foreach ($this->_columns as $column) {
                $out[] = '<td title="' . $timerId . '">' . $this->_renderColumnValue(
                    $stat->fetch($timerId, $column),
                    $column
                ) . '</td>';
            }
            $out[] = '</tr>';
        }
        $out[] = '</table>';
        $out[] = '';
        $out = implode("\n", $out);
        echo $out;
    }

    /**
     * Render timer id column value
     *
     * @param string $timerId
     * @return string
     */
    protected function _renderTimerId($timerId)
    {
        $nestingSep = preg_quote(Profiler::NESTING_SEPARATOR, '/');
        return preg_replace('/.+?' . $nestingSep . '/', '&middot;&nbsp;&nbsp;', $timerId);
    }
}
