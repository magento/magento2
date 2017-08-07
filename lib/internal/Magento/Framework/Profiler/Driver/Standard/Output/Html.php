<?php
/**
 * Class that represents profiler output in HTML format
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Profiler\Driver\Standard\Output;

use Magento\Framework\Profiler;
use Magento\Framework\Profiler\Driver\Standard\AbstractOutput;
use Magento\Framework\Profiler\Driver\Standard\Stat;

/**
 * Class \Magento\Framework\Profiler\Driver\Standard\Output\Html
 *
 */
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
        $out = [];
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
