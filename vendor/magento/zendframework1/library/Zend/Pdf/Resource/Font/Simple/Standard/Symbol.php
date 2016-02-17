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
 * @subpackage Fonts
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */


/** Internally used classes */
#require_once 'Zend/Pdf/Element/Name.php';


/** Zend_Pdf_Resource_Font_Simple_Standard */
#require_once 'Zend/Pdf/Resource/Font/Simple/Standard.php';

/**
 * Implementation for the standard PDF font Symbol.
 *
 * This class was generated automatically using the font information and metric
 * data contained in the Adobe Font Metric (AFM) files, available here:
 * {@link http://partners.adobe.com/public/developer/en/pdf/Core14_AFMs.zip}
 *
 * The PHP script used to generate this class can be found in the /tools
 * directory of the framework distribution. If you need to make modifications to
 * this class, chances are the same modifications are needed for the rest of the
 * standard fonts. You should modify the script and regenerate the classes
 * instead of changing this class file by hand.
 *
 * @package    Zend_Pdf
 * @subpackage Fonts
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Pdf_Resource_Font_Simple_Standard_Symbol extends Zend_Pdf_Resource_Font_Simple_Standard
{
  /**** Instance Variables ****/


    /**
     * Array for conversion from local encoding to special font encoding.
     * See {@link encodeString()}.
     * @var array
     */
    protected $_toFontEncoding = array(
            0x20 => "\x20",   0x21 => "\x21", 0x2200 => "\x22",   0x23 => "\x23",
          0x2203 => "\x24",   0x25 => "\x25",   0x26 => "\x26", 0x220b => "\x27",
            0x28 => "\x28",   0x29 => "\x29", 0x2217 => "\x2a",   0x2b => "\x2b",
            0x2c => "\x2c", 0x2212 => "\x2d",   0x2e => "\x2e",   0x2f => "\x2f",
            0x30 => "\x30",   0x31 => "\x31",   0x32 => "\x32",   0x33 => "\x33",
            0x34 => "\x34",   0x35 => "\x35",   0x36 => "\x36",   0x37 => "\x37",
            0x38 => "\x38",   0x39 => "\x39",   0x3a => "\x3a",   0x3b => "\x3b",
            0x3c => "\x3c",   0x3d => "\x3d",   0x3e => "\x3e",   0x3f => "\x3f",
          0x2245 => "\x40", 0x0391 => "\x41", 0x0392 => "\x42", 0x03a7 => "\x43",
          0x2206 => "\x44", 0x0395 => "\x45", 0x03a6 => "\x46", 0x0393 => "\x47",
          0x0397 => "\x48", 0x0399 => "\x49", 0x03d1 => "\x4a", 0x039a => "\x4b",
          0x039b => "\x4c", 0x039c => "\x4d", 0x039d => "\x4e", 0x039f => "\x4f",
          0x03a0 => "\x50", 0x0398 => "\x51", 0x03a1 => "\x52", 0x03a3 => "\x53",
          0x03a4 => "\x54", 0x03a5 => "\x55", 0x03c2 => "\x56", 0x2126 => "\x57",
          0x039e => "\x58", 0x03a8 => "\x59", 0x0396 => "\x5a",   0x5b => "\x5b",
          0x2234 => "\x5c",   0x5d => "\x5d", 0x22a5 => "\x5e",   0x5f => "\x5f",
          0xf8e5 => "\x60", 0x03b1 => "\x61", 0x03b2 => "\x62", 0x03c7 => "\x63",
          0x03b4 => "\x64", 0x03b5 => "\x65", 0x03c6 => "\x66", 0x03b3 => "\x67",
          0x03b7 => "\x68", 0x03b9 => "\x69", 0x03d5 => "\x6a", 0x03ba => "\x6b",
          0x03bb => "\x6c",   0xb5 => "\x6d", 0x03bd => "\x6e", 0x03bf => "\x6f",
          0x03c0 => "\x70", 0x03b8 => "\x71", 0x03c1 => "\x72", 0x03c3 => "\x73",
          0x03c4 => "\x74", 0x03c5 => "\x75", 0x03d6 => "\x76", 0x03c9 => "\x77",
          0x03be => "\x78", 0x03c8 => "\x79", 0x03b6 => "\x7a",   0x7b => "\x7b",
            0x7c => "\x7c",   0x7d => "\x7d", 0x223c => "\x7e", 0x20ac => "\xa0",
          0x03d2 => "\xa1", 0x2032 => "\xa2", 0x2264 => "\xa3", 0x2044 => "\xa4",
          0x221e => "\xa5", 0x0192 => "\xa6", 0x2663 => "\xa7", 0x2666 => "\xa8",
          0x2665 => "\xa9", 0x2660 => "\xaa", 0x2194 => "\xab", 0x2190 => "\xac",
          0x2191 => "\xad", 0x2192 => "\xae", 0x2193 => "\xaf",   0xb0 => "\xb0",
            0xb1 => "\xb1", 0x2033 => "\xb2", 0x2265 => "\xb3",   0xd7 => "\xb4",
          0x221d => "\xb5", 0x2202 => "\xb6", 0x2022 => "\xb7",   0xf7 => "\xb8",
          0x2260 => "\xb9", 0x2261 => "\xba", 0x2248 => "\xbb", 0x2026 => "\xbc",
          0xf8e6 => "\xbd", 0xf8e7 => "\xbe", 0x21b5 => "\xbf", 0x2135 => "\xc0",
          0x2111 => "\xc1", 0x211c => "\xc2", 0x2118 => "\xc3", 0x2297 => "\xc4",
          0x2295 => "\xc5", 0x2205 => "\xc6", 0x2229 => "\xc7", 0x222a => "\xc8",
          0x2283 => "\xc9", 0x2287 => "\xca", 0x2284 => "\xcb", 0x2282 => "\xcc",
          0x2286 => "\xcd", 0x2208 => "\xce", 0x2209 => "\xcf", 0x2220 => "\xd0",
          0x2207 => "\xd1", 0xf6da => "\xd2", 0xf6d9 => "\xd3", 0xf6db => "\xd4",
          0x220f => "\xd5", 0x221a => "\xd6", 0x22c5 => "\xd7",   0xac => "\xd8",
          0x2227 => "\xd9", 0x2228 => "\xda", 0x21d4 => "\xdb", 0x21d0 => "\xdc",
          0x21d1 => "\xdd", 0x21d2 => "\xde", 0x21d3 => "\xdf", 0x25ca => "\xe0",
          0x2329 => "\xe1", 0xf8e8 => "\xe2", 0xf8e9 => "\xe3", 0xf8ea => "\xe4",
          0x2211 => "\xe5", 0xf8eb => "\xe6", 0xf8ec => "\xe7", 0xf8ed => "\xe8",
          0xf8ee => "\xe9", 0xf8ef => "\xea", 0xf8f0 => "\xeb", 0xf8f1 => "\xec",
          0xf8f2 => "\xed", 0xf8f3 => "\xee", 0xf8f4 => "\xef", 0x232a => "\xf1",
          0x222b => "\xf2", 0x2320 => "\xf3", 0xf8f5 => "\xf4", 0x2321 => "\xf5",
          0xf8f6 => "\xf6", 0xf8f7 => "\xf7", 0xf8f8 => "\xf8", 0xf8f9 => "\xf9",
          0xf8fa => "\xfa", 0xf8fb => "\xfb", 0xf8fc => "\xfc", 0xf8fd => "\xfd",
          0xf8fe => "\xfe");

    /**
     * Array for conversion from special font encoding to local encoding.
     * See {@link decodeString()}.
     * @var array
     */
    protected $_fromFontEncoding = array(
            0x20 => "\x00\x20",   0x21 => "\x00\x21",   0x22 => "\x22\x00",
            0x23 => "\x00\x23",   0x24 => "\x22\x03",   0x25 => "\x00\x25",
            0x26 => "\x00\x26",   0x27 => "\x22\x0b",   0x28 => "\x00\x28",
            0x29 => "\x00\x29",   0x2a => "\x22\x17",   0x2b => "\x00\x2b",
            0x2c => "\x00\x2c",   0x2d => "\x22\x12",   0x2e => "\x00\x2e",
            0x2f => "\x00\x2f",   0x30 => "\x00\x30",   0x31 => "\x00\x31",
            0x32 => "\x00\x32",   0x33 => "\x00\x33",   0x34 => "\x00\x34",
            0x35 => "\x00\x35",   0x36 => "\x00\x36",   0x37 => "\x00\x37",
            0x38 => "\x00\x38",   0x39 => "\x00\x39",   0x3a => "\x00\x3a",
            0x3b => "\x00\x3b",   0x3c => "\x00\x3c",   0x3d => "\x00\x3d",
            0x3e => "\x00\x3e",   0x3f => "\x00\x3f",   0x40 => "\x22\x45",
            0x41 => "\x03\x91",   0x42 => "\x03\x92",   0x43 => "\x03\xa7",
            0x44 => "\x22\x06",   0x45 => "\x03\x95",   0x46 => "\x03\xa6",
            0x47 => "\x03\x93",   0x48 => "\x03\x97",   0x49 => "\x03\x99",
            0x4a => "\x03\xd1",   0x4b => "\x03\x9a",   0x4c => "\x03\x9b",
            0x4d => "\x03\x9c",   0x4e => "\x03\x9d",   0x4f => "\x03\x9f",
            0x50 => "\x03\xa0",   0x51 => "\x03\x98",   0x52 => "\x03\xa1",
            0x53 => "\x03\xa3",   0x54 => "\x03\xa4",   0x55 => "\x03\xa5",
            0x56 => "\x03\xc2",   0x57 => "\x21\x26",   0x58 => "\x03\x9e",
            0x59 => "\x03\xa8",   0x5a => "\x03\x96",   0x5b => "\x00\x5b",
            0x5c => "\x22\x34",   0x5d => "\x00\x5d",   0x5e => "\x22\xa5",
            0x5f => "\x00\x5f",   0x60 => "\xf8\xe5",   0x61 => "\x03\xb1",
            0x62 => "\x03\xb2",   0x63 => "\x03\xc7",   0x64 => "\x03\xb4",
            0x65 => "\x03\xb5",   0x66 => "\x03\xc6",   0x67 => "\x03\xb3",
            0x68 => "\x03\xb7",   0x69 => "\x03\xb9",   0x6a => "\x03\xd5",
            0x6b => "\x03\xba",   0x6c => "\x03\xbb",   0x6d => "\x00\xb5",
            0x6e => "\x03\xbd",   0x6f => "\x03\xbf",   0x70 => "\x03\xc0",
            0x71 => "\x03\xb8",   0x72 => "\x03\xc1",   0x73 => "\x03\xc3",
            0x74 => "\x03\xc4",   0x75 => "\x03\xc5",   0x76 => "\x03\xd6",
            0x77 => "\x03\xc9",   0x78 => "\x03\xbe",   0x79 => "\x03\xc8",
            0x7a => "\x03\xb6",   0x7b => "\x00\x7b",   0x7c => "\x00\x7c",
            0x7d => "\x00\x7d",   0x7e => "\x22\x3c",   0xa0 => "\x20\xac",
            0xa1 => "\x03\xd2",   0xa2 => "\x20\x32",   0xa3 => "\x22\x64",
            0xa4 => "\x20\x44",   0xa5 => "\x22\x1e",   0xa6 => "\x01\x92",
            0xa7 => "\x26\x63",   0xa8 => "\x26\x66",   0xa9 => "\x26\x65",
            0xaa => "\x26\x60",   0xab => "\x21\x94",   0xac => "\x21\x90",
            0xad => "\x21\x91",   0xae => "\x21\x92",   0xaf => "\x21\x93",
            0xb0 => "\x00\xb0",   0xb1 => "\x00\xb1",   0xb2 => "\x20\x33",
            0xb3 => "\x22\x65",   0xb4 => "\x00\xd7",   0xb5 => "\x22\x1d",
            0xb6 => "\x22\x02",   0xb7 => "\x20\x22",   0xb8 => "\x00\xf7",
            0xb9 => "\x22\x60",   0xba => "\x22\x61",   0xbb => "\x22\x48",
            0xbc => "\x20\x26",   0xbd => "\xf8\xe6",   0xbe => "\xf8\xe7",
            0xbf => "\x21\xb5",   0xc0 => "\x21\x35",   0xc1 => "\x21\x11",
            0xc2 => "\x21\x1c",   0xc3 => "\x21\x18",   0xc4 => "\x22\x97",
            0xc5 => "\x22\x95",   0xc6 => "\x22\x05",   0xc7 => "\x22\x29",
            0xc8 => "\x22\x2a",   0xc9 => "\x22\x83",   0xca => "\x22\x87",
            0xcb => "\x22\x84",   0xcc => "\x22\x82",   0xcd => "\x22\x86",
            0xce => "\x22\x08",   0xcf => "\x22\x09",   0xd0 => "\x22\x20",
            0xd1 => "\x22\x07",   0xd2 => "\xf6\xda",   0xd3 => "\xf6\xd9",
            0xd4 => "\xf6\xdb",   0xd5 => "\x22\x0f",   0xd6 => "\x22\x1a",
            0xd7 => "\x22\xc5",   0xd8 => "\x00\xac",   0xd9 => "\x22\x27",
            0xda => "\x22\x28",   0xdb => "\x21\xd4",   0xdc => "\x21\xd0",
            0xdd => "\x21\xd1",   0xde => "\x21\xd2",   0xdf => "\x21\xd3",
            0xe0 => "\x25\xca",   0xe1 => "\x23\x29",   0xe2 => "\xf8\xe8",
            0xe3 => "\xf8\xe9",   0xe4 => "\xf8\xea",   0xe5 => "\x22\x11",
            0xe6 => "\xf8\xeb",   0xe7 => "\xf8\xec",   0xe8 => "\xf8\xed",
            0xe9 => "\xf8\xee",   0xea => "\xf8\xef",   0xeb => "\xf8\xf0",
            0xec => "\xf8\xf1",   0xed => "\xf8\xf2",   0xee => "\xf8\xf3",
            0xef => "\xf8\xf4",   0xf1 => "\x23\x2a",   0xf2 => "\x22\x2b",
            0xf3 => "\x23\x20",   0xf4 => "\xf8\xf5",   0xf5 => "\x23\x21",
            0xf6 => "\xf8\xf6",   0xf7 => "\xf8\xf7",   0xf8 => "\xf8\xf8",
            0xf9 => "\xf8\xf9",   0xfa => "\xf8\xfa",   0xfb => "\xf8\xfb",
            0xfc => "\xf8\xfc",   0xfd => "\xf8\xfd",   0xfe => "\xf8\xfe",
        );



  /**** Public Interface ****/


  /* Object Lifecycle */

    /**
     * Object constructor
     */
    public function __construct()
    {
        parent::__construct();


        /* Object properties */

        /* The font names are stored internally as Unicode UTF-16BE-encoded
         * strings. Since this information is static, save unnecessary trips
         * through iconv() and just use pre-encoded hexidecimal strings.
         */
        $this->_fontNames[Zend_Pdf_Font::NAME_COPYRIGHT]['en'] =
          "\x00\x43\x00\x6f\x00\x70\x00\x79\x00\x72\x00\x69\x00\x67\x00\x68\x00"
          . "\x74\x00\x20\x00\x28\x00\x63\x00\x29\x00\x20\x00\x31\x00\x39\x00"
          . "\x38\x00\x35\x00\x2c\x00\x20\x00\x31\x00\x39\x00\x38\x00\x37\x00"
          . "\x2c\x00\x20\x00\x31\x00\x39\x00\x38\x00\x39\x00\x2c\x00\x20\x00"
          . "\x31\x00\x39\x00\x39\x00\x30\x00\x2c\x00\x20\x00\x31\x00\x39\x00"
          . "\x39\x00\x37\x00\x20\x00\x41\x00\x64\x00\x6f\x00\x62\x00\x65\x00"
          . "\x20\x00\x53\x00\x79\x00\x73\x00\x74\x00\x65\x00\x6d\x00\x73\x00"
          . "\x20\x00\x49\x00\x6e\x00\x63\x00\x6f\x00\x72\x00\x70\x00\x6f\x00"
          . "\x72\x00\x61\x00\x74\x00\x65\x00\x64\x00\x2e\x00\x20\x00\x41\x00"
          . "\x6c\x00\x6c\x00\x20\x00\x72\x00\x69\x00\x67\x00\x68\x00\x74\x00"
          . "\x73\x00\x20\x00\x72\x00\x65\x00\x73\x00\x65\x00\x72\x00\x76\x00"
          . "\x65\x00\x64\x00\x2e";
        $this->_fontNames[Zend_Pdf_Font::NAME_FAMILY]['en'] =
          "\x00\x53\x00\x79\x00\x6d\x00\x62\x00\x6f\x00\x6c";
        $this->_fontNames[Zend_Pdf_Font::NAME_STYLE]['en'] =
          "\x00\x4d\x00\x65\x00\x64\x00\x69\x00\x75\x00\x6d";
        $this->_fontNames[Zend_Pdf_Font::NAME_ID]['en'] =
          "\x00\x34\x00\x33\x00\x30\x00\x36\x00\x34";
        $this->_fontNames[Zend_Pdf_Font::NAME_FULL]['en'] =
          "\x00\x53\x00\x79\x00\x6d\x00\x62\x00\x6f\x00\x6c\x00\x20\x00\x4d\x00"
          . "\x65\x00\x64\x00\x69\x00\x75\x00\x6d";
        $this->_fontNames[Zend_Pdf_Font::NAME_VERSION]['en'] =
          "\x00\x30\x00\x30\x00\x31\x00\x2e\x00\x30\x00\x30\x00\x38";
        $this->_fontNames[Zend_Pdf_Font::NAME_POSTSCRIPT]['en'] =
          "\x00\x53\x00\x79\x00\x6d\x00\x62\x00\x6f\x00\x6c";

        $this->_isBold = false;
        $this->_isItalic = false;
        $this->_isMonospaced = false;

        $this->_underlinePosition = -100;
        $this->_underlineThickness = 50;
        $this->_strikePosition = 225;
        $this->_strikeThickness = 50;

        $this->_unitsPerEm = 1000;

        $this->_ascent  = 1000;
        $this->_descent = 0;
        $this->_lineGap = 200;

        /* The glyph numbers assigned here are synthetic; they do not match the
         * actual glyph numbers used by the font. This is not a big deal though
         * since this data never makes it to the PDF file. It is only used
         * internally for layout calculations.
         */
        $this->_glyphWidths = array(
            0x00 => 0x01f4,   0x01 =>   0xfa,   0x02 => 0x014d,   0x03 => 0x02c9,
            0x04 => 0x01f4,   0x05 => 0x0225,   0x06 => 0x0341,   0x07 => 0x030a,
            0x08 => 0x01b7,   0x09 => 0x014d,   0x0a => 0x014d,   0x0b => 0x01f4,
            0x0c => 0x0225,   0x0d =>   0xfa,   0x0e => 0x0225,   0x0f =>   0xfa,
            0x10 => 0x0116,   0x11 => 0x01f4,   0x12 => 0x01f4,   0x13 => 0x01f4,
            0x14 => 0x01f4,   0x15 => 0x01f4,   0x16 => 0x01f4,   0x17 => 0x01f4,
            0x18 => 0x01f4,   0x19 => 0x01f4,   0x1a => 0x01f4,   0x1b => 0x0116,
            0x1c => 0x0116,   0x1d => 0x0225,   0x1e => 0x0225,   0x1f => 0x0225,
            0x20 => 0x01bc,   0x21 => 0x0225,   0x22 => 0x02d2,   0x23 => 0x029b,
            0x24 => 0x02d2,   0x25 => 0x0264,   0x26 => 0x0263,   0x27 => 0x02fb,
            0x28 => 0x025b,   0x29 => 0x02d2,   0x2a => 0x014d,   0x2b => 0x0277,
            0x2c => 0x02d2,   0x2d => 0x02ae,   0x2e => 0x0379,   0x2f => 0x02d2,
            0x30 => 0x02d2,   0x31 => 0x0300,   0x32 => 0x02e5,   0x33 => 0x022c,
            0x34 => 0x0250,   0x35 => 0x0263,   0x36 => 0x02b2,   0x37 => 0x01b7,
            0x38 => 0x0300,   0x39 => 0x0285,   0x3a => 0x031b,   0x3b => 0x0263,
            0x3c => 0x014d,   0x3d => 0x035f,   0x3e => 0x014d,   0x3f => 0x0292,
            0x40 => 0x01f4,   0x41 => 0x01f4,   0x42 => 0x0277,   0x43 => 0x0225,
            0x44 => 0x0225,   0x45 => 0x01ee,   0x46 => 0x01b7,   0x47 => 0x0209,
            0x48 => 0x019b,   0x49 => 0x025b,   0x4a => 0x0149,   0x4b => 0x025b,
            0x4c => 0x0225,   0x4d => 0x0225,   0x4e => 0x0240,   0x4f => 0x0209,
            0x50 => 0x0225,   0x51 => 0x0225,   0x52 => 0x0209,   0x53 => 0x0225,
            0x54 => 0x025b,   0x55 => 0x01b7,   0x56 => 0x0240,   0x57 => 0x02c9,
            0x58 => 0x02ae,   0x59 => 0x01ed,   0x5a => 0x02ae,   0x5b => 0x01ee,
            0x5c => 0x01e0,   0x5d =>   0xc8,   0x5e => 0x01e0,   0x5f => 0x0225,
            0x60 => 0x02ee,   0x61 => 0x026c,   0x62 =>   0xf7,   0x63 => 0x0225,
            0x64 =>   0xa7,   0x65 => 0x02c9,   0x66 => 0x01f4,   0x67 => 0x02f1,
            0x68 => 0x02f1,   0x69 => 0x02f1,   0x6a => 0x02f1,   0x6b => 0x0412,
            0x6c => 0x03db,   0x6d => 0x025b,   0x6e => 0x03db,   0x6f => 0x025b,
            0x70 => 0x0190,   0x71 => 0x0225,   0x72 => 0x019b,   0x73 => 0x0225,
            0x74 => 0x0225,   0x75 => 0x02c9,   0x76 => 0x01ee,   0x77 => 0x01cc,
            0x78 => 0x0225,   0x79 => 0x0225,   0x7a => 0x0225,   0x7b => 0x0225,
            0x7c => 0x03e8,   0x7d => 0x025b,   0x7e => 0x03e8,   0x7f => 0x0292,
            0x80 => 0x0337,   0x81 => 0x02ae,   0x82 => 0x031b,   0x83 => 0x03db,
            0x84 => 0x0300,   0x85 => 0x0300,   0x86 => 0x0337,   0x87 => 0x0300,
            0x88 => 0x0300,   0x89 => 0x02c9,   0x8a => 0x02c9,   0x8b => 0x02c9,
            0x8c => 0x02c9,   0x8d => 0x02c9,   0x8e => 0x02c9,   0x8f => 0x02c9,
            0x90 => 0x0300,   0x91 => 0x02c9,   0x92 => 0x0316,   0x93 => 0x0316,
            0x94 => 0x037a,   0x95 => 0x0337,   0x96 => 0x0225,   0x97 =>   0xfa,
            0x98 => 0x02c9,   0x99 => 0x025b,   0x9a => 0x025b,   0x9b => 0x0412,
            0x9c => 0x03db,   0x9d => 0x025b,   0x9e => 0x03db,   0x9f => 0x025b,
            0xa0 => 0x01ee,   0xa1 => 0x0149,   0xa2 => 0x0316,   0xa3 => 0x0316,
            0xa4 => 0x0312,   0xa5 => 0x02c9,   0xa6 => 0x0180,   0xa7 => 0x0180,
            0xa8 => 0x0180,   0xa9 => 0x0180,   0xaa => 0x0180,   0xab => 0x0180,
            0xac => 0x01ee,   0xad => 0x01ee,   0xae => 0x01ee,   0xaf => 0x01ee,
            0xb0 => 0x0149,   0xb1 => 0x0112,   0xb2 => 0x02ae,   0xb3 => 0x02ae,
            0xb4 => 0x02ae,   0xb5 => 0x0180,   0xb6 => 0x0180,   0xb7 => 0x0180,
            0xb8 => 0x0180,   0xb9 => 0x0180,   0xba => 0x0180,   0xbb => 0x01ee,
            0xbc => 0x01ee,   0xbd => 0x01ee,   0xbe => 0x0316);

        /* The cmap table is similarly synthesized.
         */
        $cmapData = array(
            0x20 =>   0x01,   0x21 =>   0x02, 0x2200 =>   0x03,   0x23 =>   0x04,
          0x2203 =>   0x05,   0x25 =>   0x06,   0x26 =>   0x07, 0x220b =>   0x08,
            0x28 =>   0x09,   0x29 =>   0x0a, 0x2217 =>   0x0b,   0x2b =>   0x0c,
            0x2c =>   0x0d, 0x2212 =>   0x0e,   0x2e =>   0x0f,   0x2f =>   0x10,
            0x30 =>   0x11,   0x31 =>   0x12,   0x32 =>   0x13,   0x33 =>   0x14,
            0x34 =>   0x15,   0x35 =>   0x16,   0x36 =>   0x17,   0x37 =>   0x18,
            0x38 =>   0x19,   0x39 =>   0x1a,   0x3a =>   0x1b,   0x3b =>   0x1c,
            0x3c =>   0x1d,   0x3d =>   0x1e,   0x3e =>   0x1f,   0x3f =>   0x20,
          0x2245 =>   0x21, 0x0391 =>   0x22, 0x0392 =>   0x23, 0x03a7 =>   0x24,
          0x2206 =>   0x25, 0x0395 =>   0x26, 0x03a6 =>   0x27, 0x0393 =>   0x28,
          0x0397 =>   0x29, 0x0399 =>   0x2a, 0x03d1 =>   0x2b, 0x039a =>   0x2c,
          0x039b =>   0x2d, 0x039c =>   0x2e, 0x039d =>   0x2f, 0x039f =>   0x30,
          0x03a0 =>   0x31, 0x0398 =>   0x32, 0x03a1 =>   0x33, 0x03a3 =>   0x34,
          0x03a4 =>   0x35, 0x03a5 =>   0x36, 0x03c2 =>   0x37, 0x2126 =>   0x38,
          0x039e =>   0x39, 0x03a8 =>   0x3a, 0x0396 =>   0x3b,   0x5b =>   0x3c,
          0x2234 =>   0x3d,   0x5d =>   0x3e, 0x22a5 =>   0x3f,   0x5f =>   0x40,
          0xf8e5 =>   0x41, 0x03b1 =>   0x42, 0x03b2 =>   0x43, 0x03c7 =>   0x44,
          0x03b4 =>   0x45, 0x03b5 =>   0x46, 0x03c6 =>   0x47, 0x03b3 =>   0x48,
          0x03b7 =>   0x49, 0x03b9 =>   0x4a, 0x03d5 =>   0x4b, 0x03ba =>   0x4c,
          0x03bb =>   0x4d,   0xb5 =>   0x4e, 0x03bd =>   0x4f, 0x03bf =>   0x50,
          0x03c0 =>   0x51, 0x03b8 =>   0x52, 0x03c1 =>   0x53, 0x03c3 =>   0x54,
          0x03c4 =>   0x55, 0x03c5 =>   0x56, 0x03d6 =>   0x57, 0x03c9 =>   0x58,
          0x03be =>   0x59, 0x03c8 =>   0x5a, 0x03b6 =>   0x5b,   0x7b =>   0x5c,
            0x7c =>   0x5d,   0x7d =>   0x5e, 0x223c =>   0x5f, 0x20ac =>   0x60,
          0x03d2 =>   0x61, 0x2032 =>   0x62, 0x2264 =>   0x63, 0x2044 =>   0x64,
          0x221e =>   0x65, 0x0192 =>   0x66, 0x2663 =>   0x67, 0x2666 =>   0x68,
          0x2665 =>   0x69, 0x2660 =>   0x6a, 0x2194 =>   0x6b, 0x2190 =>   0x6c,
          0x2191 =>   0x6d, 0x2192 =>   0x6e, 0x2193 =>   0x6f,   0xb0 =>   0x70,
            0xb1 =>   0x71, 0x2033 =>   0x72, 0x2265 =>   0x73,   0xd7 =>   0x74,
          0x221d =>   0x75, 0x2202 =>   0x76, 0x2022 =>   0x77,   0xf7 =>   0x78,
          0x2260 =>   0x79, 0x2261 =>   0x7a, 0x2248 =>   0x7b, 0x2026 =>   0x7c,
          0xf8e6 =>   0x7d, 0xf8e7 =>   0x7e, 0x21b5 =>   0x7f, 0x2135 =>   0x80,
          0x2111 =>   0x81, 0x211c =>   0x82, 0x2118 =>   0x83, 0x2297 =>   0x84,
          0x2295 =>   0x85, 0x2205 =>   0x86, 0x2229 =>   0x87, 0x222a =>   0x88,
          0x2283 =>   0x89, 0x2287 =>   0x8a, 0x2284 =>   0x8b, 0x2282 =>   0x8c,
          0x2286 =>   0x8d, 0x2208 =>   0x8e, 0x2209 =>   0x8f, 0x2220 =>   0x90,
          0x2207 =>   0x91, 0xf6da =>   0x92, 0xf6d9 =>   0x93, 0xf6db =>   0x94,
          0x220f =>   0x95, 0x221a =>   0x96, 0x22c5 =>   0x97,   0xac =>   0x98,
          0x2227 =>   0x99, 0x2228 =>   0x9a, 0x21d4 =>   0x9b, 0x21d0 =>   0x9c,
          0x21d1 =>   0x9d, 0x21d2 =>   0x9e, 0x21d3 =>   0x9f, 0x25ca =>   0xa0,
          0x2329 =>   0xa1, 0xf8e8 =>   0xa2, 0xf8e9 =>   0xa3, 0xf8ea =>   0xa4,
          0x2211 =>   0xa5, 0xf8eb =>   0xa6, 0xf8ec =>   0xa7, 0xf8ed =>   0xa8,
          0xf8ee =>   0xa9, 0xf8ef =>   0xaa, 0xf8f0 =>   0xab, 0xf8f1 =>   0xac,
          0xf8f2 =>   0xad, 0xf8f3 =>   0xae, 0xf8f4 =>   0xaf, 0x232a =>   0xb0,
          0x222b =>   0xb1, 0x2320 =>   0xb2, 0xf8f5 =>   0xb3, 0x2321 =>   0xb4,
          0xf8f6 =>   0xb5, 0xf8f7 =>   0xb6, 0xf8f8 =>   0xb7, 0xf8f9 =>   0xb8,
          0xf8fa =>   0xb9, 0xf8fb =>   0xba, 0xf8fc =>   0xbb, 0xf8fd =>   0xbc,
          0xf8fe =>   0xbd, 0xf8ff =>   0xbe);
        #require_once 'Zend/Pdf/Cmap.php';
        $this->_cmap = Zend_Pdf_Cmap::cmapWithTypeData(
            Zend_Pdf_Cmap::TYPE_BYTE_ENCODING_STATIC, $cmapData);


        /* Resource dictionary */

        /* The resource dictionary for the standard fonts is sparse because PDF
         * viewers already have all of the metrics data. We only need to provide
         * the font name and encoding method.
         */
        $this->_resource->BaseFont = new Zend_Pdf_Element_Name('Symbol');

        /* This font has a built-in custom character encoding method. Don't
         * override with WinAnsi like the other built-in fonts or else it will
         * not work as expected.
         */
        $this->_resource->Encoding = null;
    }


  /* Information and Conversion Methods */

    /**
     * Convert string encoding from local encoding to font encoding. Overridden
     * to defeat the conversion behavior for this ornamental font.
     *
     * @param string $string
     * @param string $charEncoding Character encoding of source text.
     * @return string
     */
    public function encodeString($string, $charEncoding)
    {
        /* This isn't the optimal time to perform this conversion, but it must
         * live here until the remainder of the layout code is completed. This,
         * and the $charEncoding parameter, will go away soon...
         */
        if ($charEncoding != 'UTF-16BE') {
            $string = iconv($charEncoding, 'UTF-16BE', $string);
        }
        /**
         * @todo Properly handle characters encoded as surrogate pairs.
         */
        $encodedString = '';
        for ($i = 0; $i < strlen($string); $i++) {
            $characterCode = (ord($string[$i++]) << 8) | ord($string[$i]);
            if (isset($this->_toFontEncoding[$characterCode])) {
                $encodedString .= $this->_toFontEncoding[$characterCode];
            } else {
                /* For now, mimic the behavior in Zend_Pdf_Font::encodeString()
                 * where unknown characters are removed completely. This is not
                 * perfect, but we should be consistent. In a future revision,
                 * we will use the well-known substitution character 0x1a
                 * (Control-Z).
                 */
            }
        }
        return $encodedString;
    }

    /**
     * Convert string encoding from font encoding to local encoding. Overridden
     * to defeat the conversion behavior for this ornamental font.
     *
     * @param string $string
     * @param string $charEncoding Character encoding of resulting text.
     * @return string
     */
    public function decodeString($string, $charEncoding)
    {
        $decodedString = '';
        for ($i = 0; $i < strlen($string); $i++) {
            $characterCode = ord($string[$i]);
            if (isset($this->_fromFontEncoding[$characterCode])) {
                $decodedString .= $this->_fromFontEncoding[$characterCode];
            } else {
                /* For now, mimic the behavior in Zend_Pdf_Font::encodeString()
                 * where unknown characters are removed completely. This is not
                 * perfect, but we should be consistent. In a future revision,
                 * we will use the Unicode substitution character (U+FFFD).
                 */
            }
        }
        if ($charEncoding != 'UTF-16BE') {
            $decodedString = iconv('UTF-16BE', $charEncoding, $decodedString);
        }
        return $decodedString;
    }

    /**
     * Converts a Latin-encoded string that fakes the font's internal encoding
     * to the proper Unicode characters, in UTF-16BE encoding.
     *
     * Used to maintain backwards compatibility with the 20 year-old legacy
     * method of using this font, which is still employed by recent versions of
     * some popular word processors.
     *
     * Note that using this method adds overhead due to the additional
     * character conversion. Don't use this for new code; it is more efficient
     * to use the appropriate Unicode characters directly.
     *
     * @param string $string
     * @param string $charEncoding (optional) Character encoding of source
     *   string. Defaults to current locale.
     * @return string
     */
    public function toUnicode($string, $charEncoding = '')
    {
        /* When using these faked strings, the closest match to the font's
         * internal encoding is ISO-8859-1.
         */
        if ($charEncoding != 'ISO-8859-1') {
            $string = iconv($charEncoding, 'ISO-8859-1', $string);
        }
        return $this->decodeString($string, 'UTF-16BE');
    }
}
