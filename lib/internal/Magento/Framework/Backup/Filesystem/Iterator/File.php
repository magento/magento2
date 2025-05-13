<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Backup\Filesystem\Iterator;

/**
 * File lines iterator
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class File extends \SplFileObject
{
    /**
     * The statement that was last read during iteration
     *
     * @var string
     */
    protected $_currentStatement = '';

    /**
     * Store current statement delimiter.
     *
     * @var string
     */
    private string $statementDelimiter = ';';

    /**
     * Return current sql statement
     *
     * @return string
     */
    #[\ReturnTypeWillChange]
    public function current()
    {
        return $this->_currentStatement;
    }

    /**
     * Iterate to next sql statement in file
     *
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function next()
    {
        $this->_currentStatement = '';
        while (!$this->eof()) {
            $line = $this->fgets();
            $trimmedLine = trim($line);
            if (!empty($trimmedLine) && !$this->isDelimiterChanged($trimmedLine)) {
                $statementFinalLine = '/(?<statement>.*)' . preg_quote($this->statementDelimiter, '/') . '$/';
                if (preg_match($statementFinalLine, $trimmedLine, $matches)) {
                    $this->_currentStatement .= $matches['statement'];
                    break;
                } else {
                    $this->_currentStatement .= $line;
                }
            }
        }
    }

    /**
     * Check whether statement delimiter has been changed.
     *
     * @param string $line
     * @return bool
     */
    private function isDelimiterChanged(string $line): bool
    {
        if (preg_match('/^delimiter\s+(?<delimiter>.+)$/i', $line, $matches)) {
            $this->statementDelimiter = $matches['delimiter'];
            return true;
        }

        return false;
    }

    /**
     * Return to first statement
     *
     * @return void
     */
    #[\ReturnTypeWillChange]
    public function rewind()
    {
        parent::rewind();
        $this->next();
    }

    /**
     * Check whether provided string is comment
     *
     * @param string $line
     * @return bool
     */
    protected function _isComment($line)
    {
        return $line[0] == '#' || ($line && substr($line, 0, 2) == '--');
    }
}
