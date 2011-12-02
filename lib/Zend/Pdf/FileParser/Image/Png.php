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
 * @subpackage FileParser
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: Png.php 22653 2010-07-22 18:41:39Z mabe $
 */

/** @see Zend_Pdf_FileParser_Image */
#require_once 'Zend/Pdf/FileParser/Image.php';


/**
 * Abstract base class for Image file parsers.
 *
 * @package    Zend_Pdf
 * @subpackage FileParser
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Pdf_FileParser_Image_Png extends Zend_Pdf_FileParser_Image
{
     protected $_isPNG;
     protected $_width;
     protected $_height;
     protected $_bits;
     protected $_color;
     protected $_compression;
     protected $_preFilter;
     protected $_interlacing;

     protected $_imageData;
     protected $_paletteData;
     protected $_transparencyData;

  /**** Public Interface ****/

     public function getWidth() {
          if(!$this->_isParsed) {
               $this->parse();
          }
          return $this->_width;
     }

     public function getHeight() {
          if(!$this->_isParsed) {
               $this->parse();
          }
          return $this->_width;
     }

     public function getBitDepth() {
          if(!$this->_isParsed) {
               $this->parse();
          }
          return $this->_bits;
     }

     public function getColorSpace() {
          if(!$this->_isParsed) {
               $this->parse();
          }
          return $this->_color;
     }

     public function getCompressionStrategy() {
          if(!$this->_isParsed) {
               $this->parse();
          }
          return $this->_compression;
     }

     public function getPaethFilter() {
          if(!$this->_isParsed) {
               $this->parse();
          }
          return $this->_preFilter;
     }

     public function getInterlacingMode() {
          if(!$this->_isParsed) {
               $this->parse();
          }
          return $this->_interlacing;
     }

     public function getRawImageData() {
          if(!$this->_isParsed) {
               $this->parse();
          }
          return $this->_imageData;
     }

     public function getRawPaletteData() {
          if(!$this->_isParsed) {
               $this->parse();
          }
          return $this->_paletteData;
     }

     public function getRawTransparencyData() {
          if(!$this->_isParsed) {
               $this->parse();
          }
          return $this->_transparencyData;
     }

  /* Semi-Concrete Class Implementation */

    /**
     * Verifies that the image file is in the expected format.
     *
     * @throws Zend_Pdf_Exception
     */
    public function screen()
    {
         if ($this->_isScreened) {
             return;
         }
         return $this->_checkSignature();
    }

    /**
     * Reads and parses the image data from the file on disk.
     *
     * @throws Zend_Pdf_Exception
     */
    public function parse()
    {
        if ($this->_isParsed) {
            return;
        }

        /* Screen the font file first, if it hasn't been done yet.
        */
        $this->screen();

        $this->_parseIHDRChunk();
        $this->_parseChunks();
    }


    protected function _parseSignature() {
         $this->moveToOffset(1); //Skip the first byte (%)
         if('PNG' != $this->readBytes(3)) {
               $this->_isPNG = false;
         } else {
               $this->_isPNG = true;
         }
    }

    protected function _checkSignature() {
         if(!isset($this->_isPNG)) {
              $this->_parseSignature();
         }
         return $this->_isPNG;
    }

    protected function _parseChunks() {
         $this->moveToOffset(33); //Variable chunks start at the end of IHDR

         //Start processing chunks. If there are no more bytes to read parsing is complete.
         $size = $this->getSize();
         while($size - $this->getOffset() >= 8) {
              $chunkLength = $this->readUInt(4);
              if($chunkLength < 0 || ($chunkLength + $this->getOffset() + 4) > $size) {
                   #require_once 'Zend/Pdf/Exception.php';
                   throw new Zend_Pdf_Exception("PNG Corrupt: Invalid Chunk Size In File.");
              }

              $chunkType = $this->readBytes(4);
              $offset = $this->getOffset();

              //If we know how to process the chunk, do it here, else ignore the chunk and move on to the next
              switch($chunkType) {
                   case 'IDAT': // This chunk may appear more than once. It contains the actual image data.
                       $this->_parseIDATChunk($offset, $chunkLength);
                       break;

                   case 'PLTE': // This chunk contains the image palette.
                       $this->_parsePLTEChunk($offset, $chunkLength);
                       break;

                   case 'tRNS': // This chunk contains non-alpha channel transparency data
                       $this->_parseTRNSChunk($offset, $chunkLength);
                       break;

                   case 'IEND':
                       break 2; //End the loop too

                   //@TODO Implement the rest of the PNG chunks. (There are many not implemented here)
              }
              if($offset + $chunkLength + 4 < $size) {
                  $this->moveToOffset($offset + $chunkLength + 4); //Skip past the data finalizer. (Don't rely on the parse to leave the offsets correct)
              }
         }
         if(empty($this->_imageData)) {
              #require_once 'Zend/Pdf/Exception.php';
              throw new Zend_Pdf_Exception ( "This PNG is corrupt. All png must contain IDAT chunks." );
         }
    }

    protected function _parseIHDRChunk() {
         $this->moveToOffset(12); //IHDR must always start at offset 12 and run for 17 bytes
         if(!$this->readBytes(4) == 'IHDR') {
              #require_once 'Zend/Pdf/Exception.php';
              throw new Zend_Pdf_Exception( "This PNG is corrupt. The first chunk in a PNG file must be IHDR." );
         }
         $this->_width = $this->readUInt(4);
         $this->_height = $this->readUInt(4);
         $this->_bits = $this->readInt(1);
         $this->_color = $this->readInt(1);
         $this->_compression = $this->readInt(1);
         $this->_preFilter = $this->readInt(1);
         $this->_interlacing = $this->readInt(1);
         if($this->_interlacing != Zend_Pdf_Image::PNG_INTERLACING_DISABLED) {
              #require_once 'Zend/Pdf/Exception.php';
              throw new Zend_Pdf_Exception( "Only non-interlaced images are currently supported." );
         }
    }

    protected function _parseIDATChunk($chunkOffset, $chunkLength) {
         $this->moveToOffset($chunkOffset);
         if(!isset($this->_imageData)) {
              $this->_imageData = $this->readBytes($chunkLength);
         } else {
              $this->_imageData .= $this->readBytes($chunkLength);
         }
    }

    protected function _parsePLTEChunk($chunkOffset, $chunkLength) {
         $this->moveToOffset($chunkOffset);
         $this->_paletteData = $this->readBytes($chunkLength);
    }

    protected function _parseTRNSChunk($chunkOffset, $chunkLength) {
         $this->moveToOffset($chunkOffset);

         //Processing of tRNS data varies dependending on the color depth

         switch($this->_color) {
             case Zend_Pdf_Image::PNG_CHANNEL_GRAY:
                  $baseColor = $this->readInt(1);
                  $this->_transparencyData = array($baseColor, $baseColor);
                  break;

             case Zend_Pdf_Image::PNG_CHANNEL_RGB:

                  //@TODO Fix this hack.
                  //This parser cheats and only uses the lsb's (and only works with < 16 bit depth images)

                  /*
                       From the standard:

                       For color type 2 (truecolor), the tRNS chunk contains a single RGB color value, stored in the format:

                       Red:   2 bytes, range 0 .. (2^bitdepth)-1
                       Green: 2 bytes, range 0 .. (2^bitdepth)-1
                       Blue:  2 bytes, range 0 .. (2^bitdepth)-1

                       (If the image bit depth is less than 16, the least significant bits are used and the others are 0.)
                       Pixels of the specified color value are to be treated as transparent (equivalent to alpha value 0);
                       all other pixels are to be treated as fully opaque (alpha value 2bitdepth-1).

                  */

                  $red = $this->readInt(1);
                  $this->skipBytes(1);
                  $green = $this->readInt(1);
                  $this->skipBytes(1);
                  $blue = $this->readInt(1);

                  $this->_transparencyData = array($red, $red, $green, $green, $blue, $blue);

                  break;

             case Zend_Pdf_Image::PNG_CHANNEL_INDEXED:

                  //@TODO Fix this hack.
                  //This parser cheats too. It only masks the first color in the palette.

                  /*
                       From the standard:

                       For color type 3 (indexed color), the tRNS chunk contains a series of one-byte alpha values, corresponding to entries in the PLTE chunk:

                          Alpha for palette index 0:  1 byte
                          Alpha for palette index 1:  1 byte
                          ...etc...

                       Each entry indicates that pixels of the corresponding palette index must be treated as having the specified alpha value.
                       Alpha values have the same interpretation as in an 8-bit full alpha channel: 0 is fully transparent, 255 is fully opaque,
                       regardless of image bit depth. The tRNS chunk must not contain more alpha values than there are palette entries,
                       but tRNS can contain fewer values than there are palette entries. In this case, the alpha value for all remaining palette
                       entries is assumed to be 255. In the common case in which only palette index 0 need be made transparent, only a one-byte
                       tRNS chunk is needed.

                  */

                  $tmpData = $this->readBytes($chunkLength);
                  if(($trnsIdx = strpos($tmpData, "\0")) !== false) {
                       $this->_transparencyData = array($trnsIdx, $trnsIdx);
                  }

                  break;

             case Zend_Pdf_Image::PNG_CHANNEL_GRAY_ALPHA:
                  //Fall through to the next case
             case Zend_Pdf_Image::PNG_CHANNEL_RGB_ALPHA:
                  #require_once 'Zend/Pdf/Exception.php';
                  throw new Zend_Pdf_Exception( "tRNS chunk illegal for Alpha Channel Images" );
                  break;
         }
    }
}
