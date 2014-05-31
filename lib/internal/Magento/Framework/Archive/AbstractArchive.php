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

/**
 * Class to work with archives
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace Magento\Framework\Archive;

class AbstractArchive
{
    /**
     * Write data to file. If file can't be opened - throw exception
     *
     * @param string $destination
     * @param string $data
     * @return true
     * @throws \Exception
     */
    protected function _writeFile($destination, $data)
    {
        $destination = trim($destination);
        if (false === file_put_contents($destination, $data)) {
            throw new \Exception("Can't write to file: " . $destination);
        }
        return true;
    }

    /**
     * Read data from file. If file can't be opened, throw to exception.
     *
     * @param string $source
     * @return string
     * @throws \Magento\Framework\Exception
     */
    protected function _readFile($source)
    {
        $data = '';
        if (is_file($source) && is_readable($source)) {
            $data = @file_get_contents($source);
            if ($data === false) {
                throw new \Magento\Framework\Exception("Can't get contents from: " . $source);
            }
        }
        return $data;
    }

    /**
     * Get file name from source (URI) without last extension.
     *
     * @param string $source
     * @param bool $withExtension
     * @return string
     */
    public function getFilename($source, $withExtension = false)
    {
        $file = str_replace(dirname($source) . '/', '', $source);
        if (!$withExtension) {
            $file = substr($file, 0, strrpos($file, '.'));
        }
        return $file;
    }
}
