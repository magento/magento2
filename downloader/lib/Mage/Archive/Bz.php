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
 * @category    Mage
 * @package     Mage_Archive
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Class to work with bzip2 archives
 *
 * @category    Mage
 * @package     Mage_Archive
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Archive_Bz extends Mage_Archive_Abstract implements Mage_Archive_Interface
{

    /**
    * Pack file by BZIP2 compressor.
    *
    * @param string $source
    * @param string $destination
    * @return string
    */
    public function pack($source, $destination)
    {
        $data = $this->_readFile($source);
        $bzData = bzcompress($data, 9);
        $this->_writeFile($destination, $bzData);
        return $destination;
    }

    /**
    * Unpack file by BZIP2 compressor.
    *
    * @param string $source
    * @param string $destination
    * @return string
    */
    public function unpack($source, $destination)
    {
        $data = '';
        $bzPointer = bzopen($source, 'r' );
        if (empty($bzPointer)) {
            throw new Exception('Can\'t open BZ archive : ' . $source);
        }
        while (!feof($bzPointer)) {
            $data .= bzread($bzPointer, 131072);
        }
        bzclose($bzPointer);
        if (is_dir($destination)) {
            $file = $this->getFilename($source);
            $destination = $destination . $file;
        }
        echo $destination;
        $this->_writeFile($destination, $data);
        return $destination;
    }

}
