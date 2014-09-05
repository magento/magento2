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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\ImportExport\Model\Import\Source;

/**
 * CSV import adapter
 */
class Csv extends \Magento\ImportExport\Model\Import\AbstractSource
{
    /**
     * @var \Magento\Framework\Filesystem\File\Write
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
     * @param string $file
     * @param \Magento\Framework\Filesystem\Directory\Write $directory
     * @param string $delimiter
     * @param string $enclosure
     * @throws \LogicException
     */
    public function __construct(
        $file,
        \Magento\Framework\Filesystem\Directory\Write $directory,
        $delimiter = ',',
        $enclosure = '"'
    ) {
        try {
            $this->_file = $directory->openFile($directory->getRelativePath($file), 'r');
        } catch (\Magento\Framework\Filesystem\FilesystemException $e) {
            throw new \LogicException("Unable to open file: '{$file}'");
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
        if (is_object($this->_file)) {
            $this->_file->close();
        }
    }

    /**
     * Read next line from CSV-file
     *
     * @return array|bool
     */
    protected function _getNextRow()
    {
        return $this->_file->readCsv(0, $this->_delimiter, $this->_enclosure);
    }

    /**
     * Rewind the \Iterator to the first element (\Iterator interface)
     *
     * @return void
     */
    public function rewind()
    {
        $this->_file->seek(0);
        $this->_getNextRow();
        // skip first line with the header
        parent::rewind();
    }
}
