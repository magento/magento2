<?php
/**
 * Zend Framework
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://framework.zend.com/license/new-bsd
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@zend.com so we can send you a copy immediately.
 *
 * @category   Zend
 * @package    Zend_Pdf
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Jpeg.php 23395 2010-11-19 15:30:47Z alexander $
 */


/** Internally used classes */
#require_once 'Zend/Pdf/Element/Name.php';
#require_once 'Zend/Pdf/Element/Numeric.php';


/** Zend_Pdf_Resource_Image */
#require_once 'Zend/Pdf/Resource/Image.php';

/**
 * JPEG image
 *
 * @package    Zend_Pdf
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Pdf_Resource_Image_Jpeg extends Zend_Pdf_Resource_Image
{

    protected $_width;
    protected $_height;
    protected $_imageProperties;

    /**
     * Object constructor
     *
     * @param string $imageFileName
     * @throws Zend_Pdf_Exception
     */
    public function __construct($imageFileName)
    {
        if (!function_exists('gd_info')) {
            #require_once 'Zend/Pdf/Exception.php';
            throw new Zend_Pdf_Exception('Image extension is not installed.');
        }

        $gd_options = gd_info();
        if ( (!isset($gd_options['JPG Support'])  || $gd_options['JPG Support']  != true)  &&
             (!isset($gd_options['JPEG Support']) || $gd_options['JPEG Support'] != true)  ) {
            #require_once 'Zend/Pdf/Exception.php';
            throw new Zend_Pdf_Exception('JPG support is not configured properly.');
        }

        if (($imageInfo = getimagesize($imageFileName)) === false) {
            #require_once 'Zend/Pdf/Exception.php';
            throw new Zend_Pdf_Exception('Corrupted image or image doesn\'t exist.');
        }
        if ($imageInfo[2] != IMAGETYPE_JPEG && $imageInfo[2] != IMAGETYPE_JPEG2000) {
            #require_once 'Zend/Pdf/Exception.php';
            throw new Zend_Pdf_Exception('ImageType is not JPG');
        }

        parent::__construct();

        switch ($imageInfo['channels']) {
            case 3:
                $colorSpace = 'DeviceRGB';
                break;
            case 4:
                $colorSpace = 'DeviceCMYK';
                break;
            default:
                $colorSpace = 'DeviceGray';
                break;
        }

        $imageDictionary = $this->_resource->dictionary;
        $imageDictionary->Width            = new Zend_Pdf_Element_Numeric($imageInfo[0]);
        $imageDictionary->Height           = new Zend_Pdf_Element_Numeric($imageInfo[1]);
        $imageDictionary->ColorSpace       = new Zend_Pdf_Element_Name($colorSpace);
        $imageDictionary->BitsPerComponent = new Zend_Pdf_Element_Numeric($imageInfo['bits']);
        if ($imageInfo[2] == IMAGETYPE_JPEG) {
            $imageDictionary->Filter       = new Zend_Pdf_Element_Name('DCTDecode');
        } else if ($imageInfo[2] == IMAGETYPE_JPEG2000){
            $imageDictionary->Filter       = new Zend_Pdf_Element_Name('JPXDecode');
        }

        if (($imageFile = @fopen($imageFileName, 'rb')) === false ) {
            #require_once 'Zend/Pdf/Exception.php';
            throw new Zend_Pdf_Exception( "Can not open '$imageFileName' file for reading." );
        }
        $byteCount = filesize($imageFileName);
        $this->_resource->value = '';

        while ($byteCount > 0 && !feof($imageFile)) {
            $nextBlock = fread($imageFile, $byteCount);
            if ($nextBlock === false) {
                #require_once 'Zend/Pdf/Exception.php';
                throw new Zend_Pdf_Exception( "Error occured while '$imageFileName' file reading." );
            }

            $this->_resource->value .= $nextBlock;
            $byteCount -= strlen($nextBlock);
        }
        if ($byteCount != 0) {
            #require_once 'Zend/Pdf/Exception.php';
            throw new Zend_Pdf_Exception( "Error occured while '$imageFileName' file reading." );
        }
        fclose($imageFile);
        $this->_resource->skipFilters();

        $this->_width  = $imageInfo[0];
        $this->_height = $imageInfo[1];
        $this->_imageProperties = array();
        $this->_imageProperties['bitDepth'] = $imageInfo['bits'];
        $this->_imageProperties['jpegImageType'] = $imageInfo[2];
        $this->_imageProperties['jpegColorType'] = $imageInfo['channels'];
    }

    /**
     * Image width
     */
    public function getPixelWidth() {
        return $this->_width;
    }

    /**
     * Image height
     */
    public function getPixelHeight() {
        return $this->_height;
    }

    /**
     * Image properties
     */
    public function getProperties() {
        return $this->_imageProperties;
    }
}

