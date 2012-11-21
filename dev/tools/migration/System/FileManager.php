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
 * @category   Magento
 * @package    tools
 * @copyright  Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Tools_Migration_System_FileManager
{
    /**
     * @var Tools_Migration_System_FileReader
     */
    protected $_reader;

    /**
     * @var Tools_Migration_System_WriterInterface
     */
    protected $_writer;


    /**
     * @param Tools_Migration_System_FileReader $reader
     * @param Tools_Migration_System_WriterInterface $writer
     */
    public function __construct(
        Tools_Migration_System_FileReader $reader,
        Tools_Migration_System_WriterInterface $writer
    ) {
        $this->_reader = $reader;
        $this->_writer = $writer;
    }

    /**
     * @param string $fileName
     * @param string $contents
     */
    public function write($fileName, $contents)
    {
        $this->_writer->write($fileName, $contents);
    }

    /**
     * Remove file
     *
     * @param $fileName
     */
    public function remove($fileName)
    {
        $this->_writer->remove($fileName);
    }

    /**
     * Retrieve contents of a file
     *
     * @param string $fileName
     * @return string
     */
    public function getContents($fileName)
    {
        return $this->_reader->getContents($fileName);
    }

    /**
     * Get file list
     *
     * @param string $pattern
     * @return array
     */
    public function getFileList($pattern)
    {
        return $this->_reader->getFileList($pattern);
    }
}
