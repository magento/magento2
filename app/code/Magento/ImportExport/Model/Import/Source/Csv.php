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
 * @category    Magento
 * @package     Magento_ImportExport
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * CSV import adapter
 */
namespace Magento\ImportExport\Model\Import\Source;

class Csv extends \Magento\ImportExport\Model\Import\AbstractSource
{
    /**
     * @var resource
     */
    protected $_file;

    /**
     * @var string
     */
    protected $_delimiter = '';

    /**
     * @var string
     */
    protected $_enclosure = '';

    /**
     * Open file and detect column names
     *
     * There must be column names in the first line
     *
     * @param string $fileOrStream
     * @param string $delimiter
     * @param string $enclosure
     * @throws \LogicException
     */
    public function __construct($fileOrStream, $delimiter = ',', $enclosure = '"')
    {
        $this->_file = @fopen($fileOrStream, 'r');
        if (false === $this->_file) {
            throw new \LogicException("Unable to open file or stream: '{$fileOrStream}'");
        }
        $this->_delimiter = $delimiter;
        $this->_enclosure = $enclosure;
        parent::__construct($this->_getNextRow());
    }

    /**
     * Close file handle
     */
    public function __destruct()
    {
        if (is_resource($this->_file)) {
            fclose($this->_file);
        }
    }

    /**
     * Read next line from CSV-file
     *
     * @return array|bool
     */
    protected function _getNextRow()
    {
        return fgetcsv($this->_file, null, $this->_delimiter, $this->_enclosure);
    }

    /**
     * Rewind the \Iterator to the first element (\Iterator interface)
     */
    public function rewind()
    {
        rewind($this->_file);
        $this->_getNextRow(); // skip first line with the header
        parent::rewind();
    }
}
