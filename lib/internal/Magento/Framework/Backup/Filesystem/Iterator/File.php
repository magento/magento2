<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
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
     * Return current sql statement
     *
     * @return string
     */
    public function current()
    {
        return $this->_currentStatement;
    }

    /**
     * Iterate to next sql statement in file
     *
     * @return void
     */
    public function next()
    {
        $this->_currentStatement = '';
        while (!$this->eof()) {
            $line = $this->fgets();
            if (strlen(trim($line))) {
                $this->_currentStatement .= $line;
                if ($this->_isLineLastInCommand($line)) {
                    break;
                }
            }
        }
    }

    /**
     * Return to first statement
     *
     * @return void
     */
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
        return $line[0] == '#' || substr($line, 0, 2) == '--';
    }

    /**
     * Check is line a last in sql command
     *
     * @param string $line
     * @return bool
     */
    protected function _isLineLastInCommand($line)
    {
        $cleanLine = trim($line);
        $lineLength = strlen($cleanLine);

        $returnResult = false;
        if ($lineLength > 0) {
            $lastSymbolIndex = $lineLength - 1;
            if ($cleanLine[$lastSymbolIndex] == ';') {
                $returnResult = true;
            }
        }

        return $returnResult;
    }
}
