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
namespace Magento\ImportExport\Model\Export\Adapter;

/**
 * Abstract adapter model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
abstract class AbstractAdapter
{
    /**
     * Destination file path.
     *
     * @var string
     */
    protected $_destination;

    /**
     * Header columns names.
     *
     * @var array
     */
    protected $_headerCols = null;

    /**
     * @var \Magento\Framework\Filesystem\Directory\Write
     */
    protected $_directoryHandle;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Filesystem $filesystem
     * @param string|null $destination
     * @throws \Magento\Framework\Model\Exception
     */
    public function __construct(\Magento\Framework\App\Filesystem $filesystem, $destination = null)
    {
        $this->_directoryHandle = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem::SYS_TMP_DIR);
        if (!$destination) {
            $destination = uniqid('importexport_');
            $this->_directoryHandle->touch($destination);
        }
        if (!is_string($destination)) {
            throw new \Magento\Framework\Model\Exception(__('Destination file path must be a string'));
        }

        if (!$this->_directoryHandle->isWritable()) {
            throw new \Magento\Framework\Model\Exception(__('Destination directory is not writable'));
        }
        if ($this->_directoryHandle->isFile($destination) && !$this->_directoryHandle->isWritable($destination)) {
            throw new \Magento\Framework\Model\Exception(__('Destination file is not writable'));
        }

        $this->_destination = $destination;

        $this->_init();
    }

    /**
     * Method called as last step of object instance creation. Can be overridden in child classes.
     *
     * @return $this
     */
    protected function _init()
    {
        return $this;
    }

    /**
     * Get contents of export file
     *
     * @return string
     */
    public function getContents()
    {
        return $this->_directoryHandle->readFile($this->_destination);
    }

    /**
     * MIME-type for 'Content-Type' header
     *
     * @return string
     */
    public function getContentType()
    {
        return 'application/octet-stream';
    }

    /**
     * Return file extension for downloading
     *
     * @return string
     */
    public function getFileExtension()
    {
        return '';
    }

    /**
     * Set column names
     *
     * @param array $headerColumns
     * @return $this
     */
    public function setHeaderCols(array $headerColumns)
    {
        return $this;
    }

    /**
     * Write row data to source file
     *
     * @param array $rowData
     * @return $this
     */
    abstract public function writeRow(array $rowData);
}
