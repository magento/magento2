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
