<?php

namespace League\CLImate\TerminalObject\Dynamic;

class Progress extends DynamicTerminalObject
{
    /**
     * The total number of items involved
     *
     * @var integer $total
     */
    protected $total       = 0;

    /**
     * The current item that the progress bar represents
     *
     * @var integer $current
     */
    protected $current = 0;

    /**
     * The string length of the bar when at 100%
     *
     * @var integer $bar_str_len
     */
    protected $bar_str_len;

    /**
     * Flag indicating whether we are writing the bar for the first time
     *
     * @var boolean $first_line
     */
    protected $first_line = true;

    /**
     * If they pass in a total, set the total
     *
     * @param integer $total
     */
    public function __construct($total = null)
    {
        if ($total) {
            $this->total($total);
        }
    }

    /**
     * Set the total property
     *
     * @param  integer $total
     *
     * @return Progress
     */
    public function total($total)
    {
        $this->total = $total;

        return $this;
    }

    /**
     * Determines the current percentage we are at and re-writes the progress bar
     *
     * @param integer $current
     * @param mixed   $label
     */
    public function current($current, $label = null)
    {
        if ($this->total == 0) {
            // Avoid dividing by 0
            throw new \Exception('The progress total must be greater than zero.');
        }

        if ($current > $this->total) {
            throw new \Exception('The current is greater than the total.');
        }

        $progress_bar = $this->getProgressBar($current, $label);

        $this->output->write($this->parser->apply($progress_bar));

        $this->current = $current;
    }

    /**
     * Increments the current position we are at and re-writes the progress bar
     *
     * @param integer $increment The number of items to increment by
     * @param string $label
     */
    public function advance($increment = 1, $label = null)
    {
        $this->current($this->current + $increment, $label);
    }

    /**
     * Build the progress bar str and return it
     *
     * @param integer $current
     * @param string $label
     *
     * @return string
     */
    protected function getProgressBar($current, $label)
    {
        if ($this->first_line) {
            // Drop down a line, we are about to
            // re-write this line for the progress bar
            $this->output->write('');
            $this->first_line = false;
        }

        // Move the cursor up one line and clear it to the end
        $line_count    = (strlen($label) > 0) ? 2 : 1;

        $progress_bar  = $this->util->cursor->up($line_count);
        $progress_bar .= $this->util->cursor->startOfCurrentLine();
        $progress_bar .= $this->util->cursor->deleteCurrentLine();
        $progress_bar .= $this->getProgressBarStr($current, $label);

        return $progress_bar;
    }

    /**
     * Get the progress bar string, basically:
     * =============>             50% label
     *
     * @param integer $current
     * @param string $label
     *
     * @return string
     */
    protected function getProgressBarStr($current, $label)
    {
        $percentage = $current / $this->total;
        $bar_length = round($this->getBarStrLen() * $percentage);
        $label      = ($percentage < 1) ? $label : '';

        $bar        = $this->getBar($bar_length);
        $number     = $this->percentageFormatted($percentage);

        if ($label) {
            $label = $this->labelFormatted($label);
        }

        return trim("{$bar} {$number}{$label}");
    }

    /**
     * Get the string for the actual bar based on the current length
     *
     * @param integer $length
     *
     * @return string
     */
    protected function getBar($length)
    {
        $bar     = str_repeat('=', $length);
        $padding = str_repeat(' ', $this->getBarStrLen() - $length);

        return "{$bar}>{$padding}";
    }

    /**
     * Get the length of the bar string based on the width of the terminal window
     *
     * @return integer
     */
    protected function getBarStrLen()
    {
        if (!$this->bar_str_len) {
            // Subtract 10 because of the '> 100%' plus some padding, max 100
            $this->bar_str_len = min($this->util->width() - 10, 100);
        }

        return $this->bar_str_len;
    }

    /**
     * Format the percentage so it looks pretty
     *
     * @param integer $percentage
     */
    protected function percentageFormatted($percentage)
    {
        return round($percentage * 100) . '%';
    }

    /**
     * Format the label so it is positioned correctly
     *
     * @param string $label
     */
    protected function labelFormatted($label)
    {
        return "\n" . $this->util->cursor->startOfCurrentLine() . $this->util->cursor->deleteCurrentLine() . $label;
    }
}
