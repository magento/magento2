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
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */


/** Internally used classes */
#require_once 'Zend/Pdf/Element/Array.php';
#require_once 'Zend/Pdf/Element/Dictionary.php';
#require_once 'Zend/Pdf/Element/Name.php';
#require_once 'Zend/Pdf/Element/Numeric.php';
#require_once 'Zend/Pdf/Element/String/Binary.php';


/** Zend_Pdf_Resource_Image */
#require_once 'Zend/Pdf/Resource/Image.php';

/**
 * PNG image
 *
 * @package    Zend_Pdf
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Pdf_Resource_Image_Png extends Zend_Pdf_Resource_Image
{
    const PNG_COMPRESSION_DEFAULT_STRATEGY = 0;
    const PNG_COMPRESSION_FILTERED = 1;
    const PNG_COMPRESSION_HUFFMAN_ONLY = 2;
    const PNG_COMPRESSION_RLE = 3;

    const PNG_FILTER_NONE = 0;
    const PNG_FILTER_SUB = 1;
    const PNG_FILTER_UP = 2;
    const PNG_FILTER_AVERAGE = 3;
    const PNG_FILTER_PAETH = 4;

    const PNG_INTERLACING_DISABLED = 0;
    const PNG_INTERLACING_ENABLED = 1;

    const PNG_CHANNEL_GRAY = 0;
    const PNG_CHANNEL_RGB = 2;
    const PNG_CHANNEL_INDEXED = 3;
    const PNG_CHANNEL_GRAY_ALPHA = 4;
    const PNG_CHANNEL_RGB_ALPHA = 6;

    protected $_width;
    protected $_height;
    protected $_imageProperties;

    /**
     * Object constructor
     *
     * @param string $imageFileName
     * @throws Zend_Pdf_Exception
     * @todo Add compression conversions to support compression strategys other than PNG_COMPRESSION_DEFAULT_STRATEGY.
     * @todo Add pre-compression filtering.
     * @todo Add interlaced image handling.
     * @todo Add support for 16-bit images. Requires PDF version bump to 1.5 at least.
     * @todo Add processing for all PNG chunks defined in the spec. gAMA etc.
     * @todo Fix tRNS chunk support for Indexed Images to a SMask.
     */
    public function __construct($imageFileName)
    {
        if (($imageFile = @fopen($imageFileName, 'rb')) === false ) {
            #require_once 'Zend/Pdf/Exception.php';
            throw new Zend_Pdf_Exception( "Can not open '$imageFileName' file for reading." );
        }

        parent::__construct();

        //Check if the file is a PNG
        fseek($imageFile, 1, SEEK_CUR); //First signature byte (%)
        if ('PNG' != fread($imageFile, 3)) {
            #require_once 'Zend/Pdf/Exception.php';
            throw new Zend_Pdf_Exception('Image is not a PNG');
        }
        fseek($imageFile, 12, SEEK_CUR); //Signature bytes (Includes the IHDR chunk) IHDR processed linerarly because it doesnt contain a variable chunk length
        $wtmp = unpack('Ni',fread($imageFile, 4)); //Unpack a 4-Byte Long
        $width = $wtmp['i'];
        $htmp = unpack('Ni',fread($imageFile, 4));
        $height = $htmp['i'];
        $bits = ord(fread($imageFile, 1)); //Higher than 8 bit depths are only supported in later versions of PDF.
        $color = ord(fread($imageFile, 1));

        $compression = ord(fread($imageFile, 1));
        $prefilter = ord(fread($imageFile,1));

        if (($interlacing = ord(fread($imageFile,1))) != Zend_Pdf_Resource_Image_Png::PNG_INTERLACING_DISABLED) {
            #require_once 'Zend/Pdf/Exception.php';
            throw new Zend_Pdf_Exception( "Only non-interlaced images are currently supported." );
        }

        $this->_width = $width;
        $this->_height = $height;
        $this->_imageProperties = array();
        $this->_imageProperties['bitDepth'] = $bits;
        $this->_imageProperties['pngColorType'] = $color;
        $this->_imageProperties['pngFilterType'] = $prefilter;
        $this->_imageProperties['pngCompressionType'] = $compression;
        $this->_imageProperties['pngInterlacingType'] = $interlacing;

        fseek($imageFile, 4, SEEK_CUR); //4 Byte Ending Sequence
        $imageData = '';

        /*
         * The following loop processes PNG chunks. 4 Byte Longs are packed first give the chunk length
         * followed by the chunk signature, a four byte code. IDAT and IEND are manditory in any PNG.
         */
        while (!feof($imageFile)) {
            $chunkLengthBytes = fread($imageFile, 4);
            if ($chunkLengthBytes === false) {
                #require_once 'Zend/Pdf/Exception.php';
                throw new Zend_Pdf_Exception('Error ocuured while image file reading.');
            }

            $chunkLengthtmp = unpack('Ni', $chunkLengthBytes);
            $chunkLength    = $chunkLengthtmp['i'];
            $chunkType      = fread($imageFile, 4);
            switch($chunkType) {
                case 'IDAT': //Image Data
                    /*
                     * Reads the actual image data from the PNG file. Since we know at this point that the compression
                     * strategy is the default strategy, we also know that this data is Zip compressed. We will either copy
                     * the data directly to the PDF and provide the correct FlateDecode predictor, or decompress the data
                     * decode the filters and output the data as a raw pixel map.
                     */
                    $imageData .= fread($imageFile, $chunkLength);
                    fseek($imageFile, 4, SEEK_CUR);
                    break;

                case 'PLTE': //Palette
                    $paletteData = fread($imageFile, $chunkLength);
                    fseek($imageFile, 4, SEEK_CUR);
                    break;

                case 'tRNS': //Basic (non-alpha channel) transparency.
                    $trnsData = fread($imageFile, $chunkLength);
                    switch ($color) {
                        case Zend_Pdf_Resource_Image_Png::PNG_CHANNEL_GRAY:
                            $baseColor = ord(substr($trnsData, 1, 1));
                            $transparencyData = array(new Zend_Pdf_Element_Numeric($baseColor),
                                                      new Zend_Pdf_Element_Numeric($baseColor));
                            break;

                        case Zend_Pdf_Resource_Image_Png::PNG_CHANNEL_RGB:
                            $red = ord(substr($trnsData,1,1));
                            $green = ord(substr($trnsData,3,1));
                            $blue = ord(substr($trnsData,5,1));
                            $transparencyData = array(new Zend_Pdf_Element_Numeric($red),
                                                      new Zend_Pdf_Element_Numeric($red),
                                                      new Zend_Pdf_Element_Numeric($green),
                                                      new Zend_Pdf_Element_Numeric($green),
                                                      new Zend_Pdf_Element_Numeric($blue),
                                                      new Zend_Pdf_Element_Numeric($blue));
                            break;

                        case Zend_Pdf_Resource_Image_Png::PNG_CHANNEL_INDEXED:
                            //Find the first transparent color in the index, we will mask that. (This is a bit of a hack. This should be a SMask and mask all entries values).
                            if(($trnsIdx = strpos($trnsData, "\0")) !== false) {
                                $transparencyData = array(new Zend_Pdf_Element_Numeric($trnsIdx),
                                                          new Zend_Pdf_Element_Numeric($trnsIdx));
                            }
                            break;

                        case Zend_Pdf_Resource_Image_Png::PNG_CHANNEL_GRAY_ALPHA:
                            // Fall through to the next case

                        case Zend_Pdf_Resource_Image_Png::PNG_CHANNEL_RGB_ALPHA:
                            #require_once 'Zend/Pdf/Exception.php';
                            throw new Zend_Pdf_Exception( "tRNS chunk illegal for Alpha Channel Images" );
                            break;
                    }
                    fseek($imageFile, 4, SEEK_CUR); //4 Byte Ending Sequence
                    break;

                case 'IEND';
                    break 2; //End the loop too

                default:
                    fseek($imageFile, $chunkLength + 4, SEEK_CUR); //Skip the section
                    break;
            }
        }
        fclose($imageFile);

        $compressed = true;
        $imageDataTmp = '';
        $smaskData = '';
        switch ($color) {
            case Zend_Pdf_Resource_Image_Png::PNG_CHANNEL_RGB:
                $colorSpace = new Zend_Pdf_Element_Name('DeviceRGB');
                break;

            case Zend_Pdf_Resource_Image_Png::PNG_CHANNEL_GRAY:
                $colorSpace = new Zend_Pdf_Element_Name('DeviceGray');
                break;

            case Zend_Pdf_Resource_Image_Png::PNG_CHANNEL_INDEXED:
                if(empty($paletteData)) {
                    #require_once 'Zend/Pdf/Exception.php';
                    throw new Zend_Pdf_Exception( "PNG Corruption: No palette data read for indexed type PNG." );
                }
                $colorSpace = new Zend_Pdf_Element_Array();
                $colorSpace->items[] = new Zend_Pdf_Element_Name('Indexed');
                $colorSpace->items[] = new Zend_Pdf_Element_Name('DeviceRGB');
                $colorSpace->items[] = new Zend_Pdf_Element_Numeric((strlen($paletteData)/3-1));
                $paletteObject = $this->_objectFactory->newObject(new Zend_Pdf_Element_String_Binary($paletteData));
                $colorSpace->items[] = $paletteObject;
                break;

            case Zend_Pdf_Resource_Image_Png::PNG_CHANNEL_GRAY_ALPHA:
                /*
                 * To decode PNG's with alpha data we must create two images from one. One image will contain the Gray data
                 * the other will contain the Gray transparency overlay data. The former will become the object data and the latter
                 * will become the Shadow Mask (SMask).
                 */
                if($bits > 8) {
                    #require_once 'Zend/Pdf/Exception.php';
                    throw new Zend_Pdf_Exception("Alpha PNGs with bit depth > 8 are not yet supported");
                }

                $colorSpace = new Zend_Pdf_Element_Name('DeviceGray');

                #require_once 'Zend/Pdf/ElementFactory.php';
                $decodingObjFactory = Zend_Pdf_ElementFactory::createFactory(1);
                $decodingStream = $decodingObjFactory->newStreamObject($imageData);
                $decodingStream->dictionary->Filter      = new Zend_Pdf_Element_Name('FlateDecode');
                $decodingStream->dictionary->DecodeParms = new Zend_Pdf_Element_Dictionary();
                $decodingStream->dictionary->DecodeParms->Predictor        = new Zend_Pdf_Element_Numeric(15);
                $decodingStream->dictionary->DecodeParms->Columns          = new Zend_Pdf_Element_Numeric($width);
                $decodingStream->dictionary->DecodeParms->Colors           = new Zend_Pdf_Element_Numeric(2);   //GreyAlpha
                $decodingStream->dictionary->DecodeParms->BitsPerComponent = new Zend_Pdf_Element_Numeric($bits);
                $decodingStream->skipFilters();

                $pngDataRawDecoded = $decodingStream->value;

                //Iterate every pixel and copy out gray data and alpha channel (this will be slow)
                for($pixel = 0, $pixelcount = ($width * $height); $pixel < $pixelcount; $pixel++) {
                    $imageDataTmp .= $pngDataRawDecoded[($pixel*2)];
                    $smaskData .= $pngDataRawDecoded[($pixel*2)+1];
                }
                $compressed = false;
                $imageData  = $imageDataTmp; //Overwrite image data with the gray channel without alpha
                break;

            case Zend_Pdf_Resource_Image_Png::PNG_CHANNEL_RGB_ALPHA:
                /*
                 * To decode PNG's with alpha data we must create two images from one. One image will contain the RGB data
                 * the other will contain the Gray transparency overlay data. The former will become the object data and the latter
                 * will become the Shadow Mask (SMask).
                 */
                if($bits > 8) {
                    #require_once 'Zend/Pdf/Exception.php';
                    throw new Zend_Pdf_Exception("Alpha PNGs with bit depth > 8 are not yet supported");
                }

                $colorSpace = new Zend_Pdf_Element_Name('DeviceRGB');

                #require_once 'Zend/Pdf/ElementFactory.php';
                $decodingObjFactory = Zend_Pdf_ElementFactory::createFactory(1);
                $decodingStream = $decodingObjFactory->newStreamObject($imageData);
                $decodingStream->dictionary->Filter      = new Zend_Pdf_Element_Name('FlateDecode');
                $decodingStream->dictionary->DecodeParms = new Zend_Pdf_Element_Dictionary();
                $decodingStream->dictionary->DecodeParms->Predictor        = new Zend_Pdf_Element_Numeric(15);
                $decodingStream->dictionary->DecodeParms->Columns          = new Zend_Pdf_Element_Numeric($width);
                $decodingStream->dictionary->DecodeParms->Colors           = new Zend_Pdf_Element_Numeric(4);   //RGBA
                $decodingStream->dictionary->DecodeParms->BitsPerComponent = new Zend_Pdf_Element_Numeric($bits);
                $decodingStream->skipFilters();

                $pngDataRawDecoded = $decodingStream->value;

                //Iterate every pixel and copy out rgb data and alpha channel (this will be slow)
                for($pixel = 0, $pixelcount = ($width * $height); $pixel < $pixelcount; $pixel++) {
                    $imageDataTmp .= $pngDataRawDecoded[($pixel*4)+0] . $pngDataRawDecoded[($pixel*4)+1] . $pngDataRawDecoded[($pixel*4)+2];
                    $smaskData .= $pngDataRawDecoded[($pixel*4)+3];
                }

                $compressed = false;
                $imageData  = $imageDataTmp; //Overwrite image data with the RGB channel without alpha
                break;

            default:
                #require_once 'Zend/Pdf/Exception.php';
                throw new Zend_Pdf_Exception( "PNG Corruption: Invalid color space." );
        }

        if(empty($imageData)) {
            #require_once 'Zend/Pdf/Exception.php';
            throw new Zend_Pdf_Exception( "Corrupt PNG Image. Mandatory IDAT chunk not found." );
        }

        $imageDictionary = $this->_resource->dictionary;
        if(!empty($smaskData)) {
            /*
             * Includes the Alpha transparency data as a Gray Image, then assigns the image as the Shadow Mask for the main image data.
             */
            $smaskStream = $this->_objectFactory->newStreamObject($smaskData);
            $smaskStream->dictionary->Type             = new Zend_Pdf_Element_Name('XObject');
            $smaskStream->dictionary->Subtype          = new Zend_Pdf_Element_Name('Image');
            $smaskStream->dictionary->Width            = new Zend_Pdf_Element_Numeric($width);
            $smaskStream->dictionary->Height           = new Zend_Pdf_Element_Numeric($height);
            $smaskStream->dictionary->ColorSpace       = new Zend_Pdf_Element_Name('DeviceGray');
            $smaskStream->dictionary->BitsPerComponent = new Zend_Pdf_Element_Numeric($bits);
            $imageDictionary->SMask = $smaskStream;

            // Encode stream with FlateDecode filter
            $smaskStreamDecodeParms = array();
            $smaskStreamDecodeParms['Predictor']        = new Zend_Pdf_Element_Numeric(15);
            $smaskStreamDecodeParms['Columns']          = new Zend_Pdf_Element_Numeric($width);
            $smaskStreamDecodeParms['Colors']           = new Zend_Pdf_Element_Numeric(1);
            $smaskStreamDecodeParms['BitsPerComponent'] = new Zend_Pdf_Element_Numeric(8);
            $smaskStream->dictionary->DecodeParms  = new Zend_Pdf_Element_Dictionary($smaskStreamDecodeParms);
            $smaskStream->dictionary->Filter       = new Zend_Pdf_Element_Name('FlateDecode');
        }

        if(!empty($transparencyData)) {
            //This is experimental and not properly tested.
            $imageDictionary->Mask = new Zend_Pdf_Element_Array($transparencyData);
        }

        $imageDictionary->Width            = new Zend_Pdf_Element_Numeric($width);
        $imageDictionary->Height           = new Zend_Pdf_Element_Numeric($height);
        $imageDictionary->ColorSpace       = $colorSpace;
        $imageDictionary->BitsPerComponent = new Zend_Pdf_Element_Numeric($bits);
        $imageDictionary->Filter       = new Zend_Pdf_Element_Name('FlateDecode');

        $decodeParms = array();
        $decodeParms['Predictor']        = new Zend_Pdf_Element_Numeric(15); // Optimal prediction
        $decodeParms['Columns']          = new Zend_Pdf_Element_Numeric($width);
        $decodeParms['Colors']           = new Zend_Pdf_Element_Numeric((($color==Zend_Pdf_Resource_Image_Png::PNG_CHANNEL_RGB || $color==Zend_Pdf_Resource_Image_Png::PNG_CHANNEL_RGB_ALPHA)?(3):(1)));
        $decodeParms['BitsPerComponent'] = new Zend_Pdf_Element_Numeric($bits);
        $imageDictionary->DecodeParms  = new Zend_Pdf_Element_Dictionary($decodeParms);

        //Include only the image IDAT section data.
        $this->_resource->value = $imageData;

        //Skip double compression
        if ($compressed) {
            $this->_resource->skipFilters();
        }
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
