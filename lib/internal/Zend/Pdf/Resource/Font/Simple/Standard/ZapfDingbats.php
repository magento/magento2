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
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id: ZapfDingbats.php 20096 2010-01-06 02:05:09Z bkarwin $
 */


/** Internally used classes */
#require_once 'Zend/Pdf/Element/Name.php';


/** Zend_Pdf_Resource_Font_Simple_Standard */
#require_once 'Zend/Pdf/Resource/Font/Simple/Standard.php';

/**
 * Implementation for the standard PDF font ZapfDingbats.
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
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Pdf_Resource_Font_Simple_Standard_ZapfDingbats extends Zend_Pdf_Resource_Font_Simple_Standard
{
  /**** Instance Variables ****/


    /**
     * Array for conversion from local encoding to special font encoding.
     * See {@link encodeString()}.
     * @var array
     */
    protected $_toFontEncoding = array(
            0x20 => "\x20", 0x2701 => "\x21", 0x2702 => "\x22", 0x2703 => "\x23",
          0x2704 => "\x24", 0x260e => "\x25", 0x2706 => "\x26", 0x2707 => "\x27",
          0x2708 => "\x28", 0x2709 => "\x29", 0x261b => "\x2a", 0x261e => "\x2b",
          0x270c => "\x2c", 0x270d => "\x2d", 0x270e => "\x2e", 0x270f => "\x2f",
          0x2710 => "\x30", 0x2711 => "\x31", 0x2712 => "\x32", 0x2713 => "\x33",
          0x2714 => "\x34", 0x2715 => "\x35", 0x2716 => "\x36", 0x2717 => "\x37",
          0x2718 => "\x38", 0x2719 => "\x39", 0x271a => "\x3a", 0x271b => "\x3b",
          0x271c => "\x3c", 0x271d => "\x3d", 0x271e => "\x3e", 0x271f => "\x3f",
          0x2720 => "\x40", 0x2721 => "\x41", 0x2722 => "\x42", 0x2723 => "\x43",
          0x2724 => "\x44", 0x2725 => "\x45", 0x2726 => "\x46", 0x2727 => "\x47",
          0x2605 => "\x48", 0x2729 => "\x49", 0x272a => "\x4a", 0x272b => "\x4b",
          0x272c => "\x4c", 0x272d => "\x4d", 0x272e => "\x4e", 0x272f => "\x4f",
          0x2730 => "\x50", 0x2731 => "\x51", 0x2732 => "\x52", 0x2733 => "\x53",
          0x2734 => "\x54", 0x2735 => "\x55", 0x2736 => "\x56", 0x2737 => "\x57",
          0x2738 => "\x58", 0x2739 => "\x59", 0x273a => "\x5a", 0x273b => "\x5b",
          0x273c => "\x5c", 0x273d => "\x5d", 0x273e => "\x5e", 0x273f => "\x5f",
          0x2740 => "\x60", 0x2741 => "\x61", 0x2742 => "\x62", 0x2743 => "\x63",
          0x2744 => "\x64", 0x2745 => "\x65", 0x2746 => "\x66", 0x2747 => "\x67",
          0x2748 => "\x68", 0x2749 => "\x69", 0x274a => "\x6a", 0x274b => "\x6b",
          0x25cf => "\x6c", 0x274d => "\x6d", 0x25a0 => "\x6e", 0x274f => "\x6f",
          0x2750 => "\x70", 0x2751 => "\x71", 0x2752 => "\x72", 0x25b2 => "\x73",
          0x25bc => "\x74", 0x25c6 => "\x75", 0x2756 => "\x76", 0x25d7 => "\x77",
          0x2758 => "\x78", 0x2759 => "\x79", 0x275a => "\x7a", 0x275b => "\x7b",
          0x275c => "\x7c", 0x275d => "\x7d", 0x275e => "\x7e", 0x2768 => "\x80",
          0x2769 => "\x81", 0x276a => "\x82", 0x276b => "\x83", 0x276c => "\x84",
          0x276d => "\x85", 0x276e => "\x86", 0x276f => "\x87", 0x2770 => "\x88",
          0x2771 => "\x89", 0x2772 => "\x8a", 0x2773 => "\x8b", 0x2774 => "\x8c",
          0x2775 => "\x8d", 0x2761 => "\xa1", 0x2762 => "\xa2", 0x2763 => "\xa3",
          0x2764 => "\xa4", 0x2765 => "\xa5", 0x2766 => "\xa6", 0x2767 => "\xa7",
          0x2663 => "\xa8", 0x2666 => "\xa9", 0x2665 => "\xaa", 0x2660 => "\xab",
          0x2460 => "\xac", 0x2461 => "\xad", 0x2462 => "\xae", 0x2463 => "\xaf",
          0x2464 => "\xb0", 0x2465 => "\xb1", 0x2466 => "\xb2", 0x2467 => "\xb3",
          0x2468 => "\xb4", 0x2469 => "\xb5", 0x2776 => "\xb6", 0x2777 => "\xb7",
          0x2778 => "\xb8", 0x2779 => "\xb9", 0x277a => "\xba", 0x277b => "\xbb",
          0x277c => "\xbc", 0x277d => "\xbd", 0x277e => "\xbe", 0x277f => "\xbf",
          0x2780 => "\xc0", 0x2781 => "\xc1", 0x2782 => "\xc2", 0x2783 => "\xc3",
          0x2784 => "\xc4", 0x2785 => "\xc5", 0x2786 => "\xc6", 0x2787 => "\xc7",
          0x2788 => "\xc8", 0x2789 => "\xc9", 0x278a => "\xca", 0x278b => "\xcb",
          0x278c => "\xcc", 0x278d => "\xcd", 0x278e => "\xce", 0x278f => "\xcf",
          0x2790 => "\xd0", 0x2791 => "\xd1", 0x2792 => "\xd2", 0x2793 => "\xd3",
          0x2794 => "\xd4", 0x2192 => "\xd5", 0x2194 => "\xd6", 0x2195 => "\xd7",
          0x2798 => "\xd8", 0x2799 => "\xd9", 0x279a => "\xda", 0x279b => "\xdb",
          0x279c => "\xdc", 0x279d => "\xdd", 0x279e => "\xde", 0x279f => "\xdf",
          0x27a0 => "\xe0", 0x27a1 => "\xe1", 0x27a2 => "\xe2", 0x27a3 => "\xe3",
          0x27a4 => "\xe4", 0x27a5 => "\xe5", 0x27a6 => "\xe6", 0x27a7 => "\xe7",
          0x27a8 => "\xe8", 0x27a9 => "\xe9", 0x27aa => "\xea", 0x27ab => "\xeb",
          0x27ac => "\xec", 0x27ad => "\xed", 0x27ae => "\xee", 0x27af => "\xef",
          0x27b1 => "\xf1", 0x27b2 => "\xf2", 0x27b3 => "\xf3", 0x27b4 => "\xf4",
          0x27b5 => "\xf5", 0x27b6 => "\xf6", 0x27b7 => "\xf7", 0x27b8 => "\xf8",
          0x27b9 => "\xf9", 0x27ba => "\xfa", 0x27bb => "\xfb", 0x27bc => "\xfc",
          0x27bd => "\xfd", 0x27be => "\xfe");

    /**
     * Array for conversion from special font encoding to local encoding.
     * See {@link decodeString()}.
     * @var array
     */
    protected $_fromFontEncoding = array(
            0x20 => "\x00\x20",   0x21 => "\x27\x01",   0x22 => "\x27\x02",
            0x23 => "\x27\x03",   0x24 => "\x27\x04",   0x25 => "\x26\x0e",
            0x26 => "\x27\x06",   0x27 => "\x27\x07",   0x28 => "\x27\x08",
            0x29 => "\x27\x09",   0x2a => "\x26\x1b",   0x2b => "\x26\x1e",
            0x2c => "\x27\x0c",   0x2d => "\x27\x0d",   0x2e => "\x27\x0e",
            0x2f => "\x27\x0f",   0x30 => "\x27\x10",   0x31 => "\x27\x11",
            0x32 => "\x27\x12",   0x33 => "\x27\x13",   0x34 => "\x27\x14",
            0x35 => "\x27\x15",   0x36 => "\x27\x16",   0x37 => "\x27\x17",
            0x38 => "\x27\x18",   0x39 => "\x27\x19",   0x3a => "\x27\x1a",
            0x3b => "\x27\x1b",   0x3c => "\x27\x1c",   0x3d => "\x27\x1d",
            0x3e => "\x27\x1e",   0x3f => "\x27\x1f",   0x40 => "\x27\x20",
            0x41 => "\x27\x21",   0x42 => "\x27\x22",   0x43 => "\x27\x23",
            0x44 => "\x27\x24",   0x45 => "\x27\x25",   0x46 => "\x27\x26",
            0x47 => "\x27\x27",   0x48 => "\x26\x05",   0x49 => "\x27\x29",
            0x4a => "\x27\x2a",   0x4b => "\x27\x2b",   0x4c => "\x27\x2c",
            0x4d => "\x27\x2d",   0x4e => "\x27\x2e",   0x4f => "\x27\x2f",
            0x50 => "\x27\x30",   0x51 => "\x27\x31",   0x52 => "\x27\x32",
            0x53 => "\x27\x33",   0x54 => "\x27\x34",   0x55 => "\x27\x35",
            0x56 => "\x27\x36",   0x57 => "\x27\x37",   0x58 => "\x27\x38",
            0x59 => "\x27\x39",   0x5a => "\x27\x3a",   0x5b => "\x27\x3b",
            0x5c => "\x27\x3c",   0x5d => "\x27\x3d",   0x5e => "\x27\x3e",
            0x5f => "\x27\x3f",   0x60 => "\x27\x40",   0x61 => "\x27\x41",
            0x62 => "\x27\x42",   0x63 => "\x27\x43",   0x64 => "\x27\x44",
            0x65 => "\x27\x45",   0x66 => "\x27\x46",   0x67 => "\x27\x47",
            0x68 => "\x27\x48",   0x69 => "\x27\x49",   0x6a => "\x27\x4a",
            0x6b => "\x27\x4b",   0x6c => "\x25\xcf",   0x6d => "\x27\x4d",
            0x6e => "\x25\xa0",   0x6f => "\x27\x4f",   0x70 => "\x27\x50",
            0x71 => "\x27\x51",   0x72 => "\x27\x52",   0x73 => "\x25\xb2",
            0x74 => "\x25\xbc",   0x75 => "\x25\xc6",   0x76 => "\x27\x56",
            0x77 => "\x25\xd7",   0x78 => "\x27\x58",   0x79 => "\x27\x59",
            0x7a => "\x27\x5a",   0x7b => "\x27\x5b",   0x7c => "\x27\x5c",
            0x7d => "\x27\x5d",   0x7e => "\x27\x5e",   0x80 => "\x27\x68",
            0x81 => "\x27\x69",   0x82 => "\x27\x6a",   0x83 => "\x27\x6b",
            0x84 => "\x27\x6c",   0x85 => "\x27\x6d",   0x86 => "\x27\x6e",
            0x87 => "\x27\x6f",   0x88 => "\x27\x70",   0x89 => "\x27\x71",
            0x8a => "\x27\x72",   0x8b => "\x27\x73",   0x8c => "\x27\x74",
            0x8d => "\x27\x75",   0xa1 => "\x27\x61",   0xa2 => "\x27\x62",
            0xa3 => "\x27\x63",   0xa4 => "\x27\x64",   0xa5 => "\x27\x65",
            0xa6 => "\x27\x66",   0xa7 => "\x27\x67",   0xa8 => "\x26\x63",
            0xa9 => "\x26\x66",   0xaa => "\x26\x65",   0xab => "\x26\x60",
            0xac => "\x24\x60",   0xad => "\x24\x61",   0xae => "\x24\x62",
            0xaf => "\x24\x63",   0xb0 => "\x24\x64",   0xb1 => "\x24\x65",
            0xb2 => "\x24\x66",   0xb3 => "\x24\x67",   0xb4 => "\x24\x68",
            0xb5 => "\x24\x69",   0xb6 => "\x27\x76",   0xb7 => "\x27\x77",
            0xb8 => "\x27\x78",   0xb9 => "\x27\x79",   0xba => "\x27\x7a",
            0xbb => "\x27\x7b",   0xbc => "\x27\x7c",   0xbd => "\x27\x7d",
            0xbe => "\x27\x7e",   0xbf => "\x27\x7f",   0xc0 => "\x27\x80",
            0xc1 => "\x27\x81",   0xc2 => "\x27\x82",   0xc3 => "\x27\x83",
            0xc4 => "\x27\x84",   0xc5 => "\x27\x85",   0xc6 => "\x27\x86",
            0xc7 => "\x27\x87",   0xc8 => "\x27\x88",   0xc9 => "\x27\x89",
            0xca => "\x27\x8a",   0xcb => "\x27\x8b",   0xcc => "\x27\x8c",
            0xcd => "\x27\x8d",   0xce => "\x27\x8e",   0xcf => "\x27\x8f",
            0xd0 => "\x27\x90",   0xd1 => "\x27\x91",   0xd2 => "\x27\x92",
            0xd3 => "\x27\x93",   0xd4 => "\x27\x94",   0xd5 => "\x21\x92",
            0xd6 => "\x21\x94",   0xd7 => "\x21\x95",   0xd8 => "\x27\x98",
            0xd9 => "\x27\x99",   0xda => "\x27\x9a",   0xdb => "\x27\x9b",
            0xdc => "\x27\x9c",   0xdd => "\x27\x9d",   0xde => "\x27\x9e",
            0xdf => "\x27\x9f",   0xe0 => "\x27\xa0",   0xe1 => "\x27\xa1",
            0xe2 => "\x27\xa2",   0xe3 => "\x27\xa3",   0xe4 => "\x27\xa4",
            0xe5 => "\x27\xa5",   0xe6 => "\x27\xa6",   0xe7 => "\x27\xa7",
            0xe8 => "\x27\xa8",   0xe9 => "\x27\xa9",   0xea => "\x27\xaa",
            0xeb => "\x27\xab",   0xec => "\x27\xac",   0xed => "\x27\xad",
            0xee => "\x27\xae",   0xef => "\x27\xaf",   0xf1 => "\x27\xb1",
            0xf2 => "\x27\xb2",   0xf3 => "\x27\xb3",   0xf4 => "\x27\xb4",
            0xf5 => "\x27\xb5",   0xf6 => "\x27\xb6",   0xf7 => "\x27\xb7",
            0xf8 => "\x27\xb8",   0xf9 => "\x27\xb9",   0xfa => "\x27\xba",
            0xfb => "\x27\xbb",   0xfc => "\x27\xbc",   0xfd => "\x27\xbd",
            0xfe => "\x27\xbe");



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
          . "\x2c\x00\x20\x00\x31\x00\x39\x00\x38\x00\x38\x00\x2c\x00\x20\x00"
          . "\x31\x00\x39\x00\x38\x00\x39\x00\x2c\x00\x20\x00\x31\x00\x39\x00"
          . "\x39\x00\x37\x00\x20\x00\x41\x00\x64\x00\x6f\x00\x62\x00\x65\x00"
          . "\x20\x00\x53\x00\x79\x00\x73\x00\x74\x00\x65\x00\x6d\x00\x73\x00"
          . "\x20\x00\x49\x00\x6e\x00\x63\x00\x6f\x00\x72\x00\x70\x00\x6f\x00"
          . "\x72\x00\x61\x00\x74\x00\x65\x00\x64\x00\x2e\x00\x20\x00\x41\x00"
          . "\x6c\x00\x6c\x00\x20\x00\x52\x00\x69\x00\x67\x00\x68\x00\x74\x00"
          . "\x73\x00\x20\x00\x52\x00\x65\x00\x73\x00\x65\x00\x72\x00\x76\x00"
          . "\x65\x00\x64\x00\x2e\x00\x49\x00\x54\x00\x43\x00\x20\x00\x5a\x00"
          . "\x61\x00\x70\x00\x66\x00\x20\x00\x44\x00\x69\x00\x6e\x00\x67\x00"
          . "\x62\x00\x61\x00\x74\x00\x73\x00\x20\x00\x69\x00\x73\x00\x20\x00"
          . "\x61\x00\x20\x00\x72\x00\x65\x00\x67\x00\x69\x00\x73\x00\x74\x00"
          . "\x65\x00\x72\x00\x65\x00\x64\x00\x20\x00\x74\x00\x72\x00\x61\x00"
          . "\x64\x00\x65\x00\x6d\x00\x61\x00\x72\x00\x6b\x00\x20\x00\x6f\x00"
          . "\x66\x00\x20\x00\x49\x00\x6e\x00\x74\x00\x65\x00\x72\x00\x6e\x00"
          . "\x61\x00\x74\x00\x69\x00\x6f\x00\x6e\x00\x61\x00\x6c\x00\x20\x00"
          . "\x54\x00\x79\x00\x70\x00\x65\x00\x66\x00\x61\x00\x63\x00\x65\x00"
          . "\x20\x00\x43\x00\x6f\x00\x72\x00\x70\x00\x6f\x00\x72\x00\x61\x00"
          . "\x74\x00\x69\x00\x6f\x00\x6e\x00\x2e";
        $this->_fontNames[Zend_Pdf_Font::NAME_FAMILY]['en'] =
          "\x00\x5a\x00\x61\x00\x70\x00\x66\x00\x44\x00\x69\x00\x6e\x00\x67\x00"
          . "\x62\x00\x61\x00\x74\x00\x73";
        $this->_fontNames[Zend_Pdf_Font::NAME_STYLE]['en'] =
          "\x00\x4d\x00\x65\x00\x64\x00\x69\x00\x75\x00\x6d";
        $this->_fontNames[Zend_Pdf_Font::NAME_ID]['en'] =
          "\x00\x34\x00\x33\x00\x30\x00\x38\x00\x32";
        $this->_fontNames[Zend_Pdf_Font::NAME_FULL]['en'] =
          "\x00\x5a\x00\x61\x00\x70\x00\x66\x00\x44\x00\x69\x00\x6e\x00\x67\x00"
          . "\x62\x00\x61\x00\x74\x00\x73\x00\x20\x00\x4d\x00\x65\x00\x64\x00"
          . "\x69\x00\x75\x00\x6d";
        $this->_fontNames[Zend_Pdf_Font::NAME_VERSION]['en'] =
          "\x00\x30\x00\x30\x00\x32\x00\x2e\x00\x30\x00\x30\x00\x30";
        $this->_fontNames[Zend_Pdf_Font::NAME_POSTSCRIPT]['en'] =
          "\x00\x5a\x00\x61\x00\x70\x00\x66\x00\x44\x00\x69\x00\x6e\x00\x67\x00"
          . "\x62\x00\x61\x00\x74\x00\x73";

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
            0x00 => 0x01f4,   0x01 => 0x0116,   0x02 => 0x03ce,   0x03 => 0x03c1,
            0x04 => 0x03ce,   0x05 => 0x03d4,   0x06 => 0x02cf,   0x07 => 0x0315,
            0x08 => 0x0316,   0x09 => 0x0317,   0x0a => 0x02b2,   0x0b => 0x03c0,
            0x0c => 0x03ab,   0x0d => 0x0225,   0x0e => 0x0357,   0x0f => 0x038f,
            0x10 => 0x03a5,   0x11 => 0x038f,   0x12 => 0x03b1,   0x13 => 0x03ce,
            0x14 => 0x02f3,   0x15 => 0x034e,   0x16 => 0x02fa,   0x17 => 0x02f9,
            0x18 => 0x023b,   0x19 => 0x02a5,   0x1a => 0x02fb,   0x1b => 0x02f8,
            0x1c => 0x02f7,   0x1d => 0x02f2,   0x1e => 0x01ee,   0x1f => 0x0228,
            0x20 => 0x0219,   0x21 => 0x0241,   0x22 => 0x02b4,   0x23 => 0x0312,
            0x24 => 0x0314,   0x25 => 0x0314,   0x26 => 0x0316,   0x27 => 0x0319,
            0x28 => 0x031a,   0x29 => 0x0330,   0x2a => 0x0337,   0x2b => 0x0315,
            0x2c => 0x0349,   0x2d => 0x0337,   0x2e => 0x0341,   0x2f => 0x0330,
            0x30 => 0x033f,   0x31 => 0x039b,   0x32 => 0x02e8,   0x33 => 0x02d3,
            0x34 => 0x02ed,   0x35 => 0x0316,   0x36 => 0x0318,   0x37 => 0x02b7,
            0x38 => 0x0308,   0x39 => 0x0300,   0x3a => 0x0318,   0x3b => 0x02f7,
            0x3c => 0x02c3,   0x3d => 0x02c4,   0x3e => 0x02aa,   0x3f => 0x02bd,
            0x40 => 0x033a,   0x41 => 0x032f,   0x42 => 0x0315,   0x43 => 0x0315,
            0x44 => 0x02c3,   0x45 => 0x02af,   0x46 => 0x02b8,   0x47 => 0x02b1,
            0x48 => 0x0312,   0x49 => 0x0313,   0x4a => 0x02c9,   0x4b => 0x0317,
            0x4c => 0x0311,   0x4d => 0x0317,   0x4e => 0x0369,   0x4f => 0x02f9,
            0x50 => 0x02fa,   0x51 => 0x02fa,   0x52 => 0x02f7,   0x53 => 0x02f7,
            0x54 => 0x037c,   0x55 => 0x037c,   0x56 => 0x0314,   0x57 => 0x0310,
            0x58 => 0x01b6,   0x59 =>   0x8a,   0x5a => 0x0115,   0x5b => 0x019f,
            0x5c => 0x0188,   0x5d => 0x0188,   0x5e => 0x029c,   0x5f => 0x029c,
            0x60 => 0x0186,   0x61 => 0x0186,   0x62 => 0x013d,   0x63 => 0x013d,
            0x64 => 0x0114,   0x65 => 0x0114,   0x66 => 0x01fd,   0x67 => 0x01fd,
            0x68 => 0x019a,   0x69 => 0x019a,   0x6a =>   0xea,   0x6b =>   0xea,
            0x6c => 0x014e,   0x6d => 0x014e,   0x6e => 0x02dc,   0x6f => 0x0220,
            0x70 => 0x0220,   0x71 => 0x038e,   0x72 => 0x029b,   0x73 => 0x02f8,
            0x74 => 0x02f8,   0x75 => 0x0308,   0x76 => 0x0253,   0x77 => 0x02b6,
            0x78 => 0x0272,   0x79 => 0x0314,   0x7a => 0x0314,   0x7b => 0x0314,
            0x7c => 0x0314,   0x7d => 0x0314,   0x7e => 0x0314,   0x7f => 0x0314,
            0x80 => 0x0314,   0x81 => 0x0314,   0x82 => 0x0314,   0x83 => 0x0314,
            0x84 => 0x0314,   0x85 => 0x0314,   0x86 => 0x0314,   0x87 => 0x0314,
            0x88 => 0x0314,   0x89 => 0x0314,   0x8a => 0x0314,   0x8b => 0x0314,
            0x8c => 0x0314,   0x8d => 0x0314,   0x8e => 0x0314,   0x8f => 0x0314,
            0x90 => 0x0314,   0x91 => 0x0314,   0x92 => 0x0314,   0x93 => 0x0314,
            0x94 => 0x0314,   0x95 => 0x0314,   0x96 => 0x0314,   0x97 => 0x0314,
            0x98 => 0x0314,   0x99 => 0x0314,   0x9a => 0x0314,   0x9b => 0x0314,
            0x9c => 0x0314,   0x9d => 0x0314,   0x9e => 0x0314,   0x9f => 0x0314,
            0xa0 => 0x0314,   0xa1 => 0x037e,   0xa2 => 0x0346,   0xa3 => 0x03f8,
            0xa4 => 0x01ca,   0xa5 => 0x02ec,   0xa6 => 0x039c,   0xa7 => 0x02ec,
            0xa8 => 0x0396,   0xa9 => 0x039f,   0xaa => 0x03a0,   0xab => 0x03a0,
            0xac => 0x0342,   0xad => 0x0369,   0xae => 0x033c,   0xaf => 0x039c,
            0xb0 => 0x039c,   0xb1 => 0x0395,   0xb2 => 0x03a2,   0xb3 => 0x03a3,
            0xb4 => 0x01cf,   0xb5 => 0x0373,   0xb6 => 0x0344,   0xb7 => 0x0344,
            0xb8 => 0x0363,   0xb9 => 0x0363,   0xba => 0x02b8,   0xbb => 0x02b8,
            0xbc => 0x036a,   0xbd => 0x036a,   0xbe => 0x02f8,   0xbf => 0x03b2,
            0xc0 => 0x0303,   0xc1 => 0x0361,   0xc2 => 0x0303,   0xc3 => 0x0378,
            0xc4 => 0x03c7,   0xc5 => 0x0378,   0xc6 => 0x033f,   0xc7 => 0x0369,
            0xc8 => 0x039f,   0xc9 => 0x03ca,   0xca => 0x0396);

        /* The cmap table is similarly synthesized.
         */
        $cmapData = array(
            0x20 =>   0x01, 0x2701 =>   0x02, 0x2702 =>   0x03, 0x2703 =>   0x04,
          0x2704 =>   0x05, 0x260e =>   0x06, 0x2706 =>   0x07, 0x2707 =>   0x08,
          0x2708 =>   0x09, 0x2709 =>   0x0a, 0x261b =>   0x0b, 0x261e =>   0x0c,
          0x270c =>   0x0d, 0x270d =>   0x0e, 0x270e =>   0x0f, 0x270f =>   0x10,
          0x2710 =>   0x11, 0x2711 =>   0x12, 0x2712 =>   0x13, 0x2713 =>   0x14,
          0x2714 =>   0x15, 0x2715 =>   0x16, 0x2716 =>   0x17, 0x2717 =>   0x18,
          0x2718 =>   0x19, 0x2719 =>   0x1a, 0x271a =>   0x1b, 0x271b =>   0x1c,
          0x271c =>   0x1d, 0x271d =>   0x1e, 0x271e =>   0x1f, 0x271f =>   0x20,
          0x2720 =>   0x21, 0x2721 =>   0x22, 0x2722 =>   0x23, 0x2723 =>   0x24,
          0x2724 =>   0x25, 0x2725 =>   0x26, 0x2726 =>   0x27, 0x2727 =>   0x28,
          0x2605 =>   0x29, 0x2729 =>   0x2a, 0x272a =>   0x2b, 0x272b =>   0x2c,
          0x272c =>   0x2d, 0x272d =>   0x2e, 0x272e =>   0x2f, 0x272f =>   0x30,
          0x2730 =>   0x31, 0x2731 =>   0x32, 0x2732 =>   0x33, 0x2733 =>   0x34,
          0x2734 =>   0x35, 0x2735 =>   0x36, 0x2736 =>   0x37, 0x2737 =>   0x38,
          0x2738 =>   0x39, 0x2739 =>   0x3a, 0x273a =>   0x3b, 0x273b =>   0x3c,
          0x273c =>   0x3d, 0x273d =>   0x3e, 0x273e =>   0x3f, 0x273f =>   0x40,
          0x2740 =>   0x41, 0x2741 =>   0x42, 0x2742 =>   0x43, 0x2743 =>   0x44,
          0x2744 =>   0x45, 0x2745 =>   0x46, 0x2746 =>   0x47, 0x2747 =>   0x48,
          0x2748 =>   0x49, 0x2749 =>   0x4a, 0x274a =>   0x4b, 0x274b =>   0x4c,
          0x25cf =>   0x4d, 0x274d =>   0x4e, 0x25a0 =>   0x4f, 0x274f =>   0x50,
          0x2750 =>   0x51, 0x2751 =>   0x52, 0x2752 =>   0x53, 0x25b2 =>   0x54,
          0x25bc =>   0x55, 0x25c6 =>   0x56, 0x2756 =>   0x57, 0x25d7 =>   0x58,
          0x2758 =>   0x59, 0x2759 =>   0x5a, 0x275a =>   0x5b, 0x275b =>   0x5c,
          0x275c =>   0x5d, 0x275d =>   0x5e, 0x275e =>   0x5f, 0x2768 =>   0x60,
          0x2769 =>   0x61, 0x276a =>   0x62, 0x276b =>   0x63, 0x276c =>   0x64,
          0x276d =>   0x65, 0x276e =>   0x66, 0x276f =>   0x67, 0x2770 =>   0x68,
          0x2771 =>   0x69, 0x2772 =>   0x6a, 0x2773 =>   0x6b, 0x2774 =>   0x6c,
          0x2775 =>   0x6d, 0x2761 =>   0x6e, 0x2762 =>   0x6f, 0x2763 =>   0x70,
          0x2764 =>   0x71, 0x2765 =>   0x72, 0x2766 =>   0x73, 0x2767 =>   0x74,
          0x2663 =>   0x75, 0x2666 =>   0x76, 0x2665 =>   0x77, 0x2660 =>   0x78,
          0x2460 =>   0x79, 0x2461 =>   0x7a, 0x2462 =>   0x7b, 0x2463 =>   0x7c,
          0x2464 =>   0x7d, 0x2465 =>   0x7e, 0x2466 =>   0x7f, 0x2467 =>   0x80,
          0x2468 =>   0x81, 0x2469 =>   0x82, 0x2776 =>   0x83, 0x2777 =>   0x84,
          0x2778 =>   0x85, 0x2779 =>   0x86, 0x277a =>   0x87, 0x277b =>   0x88,
          0x277c =>   0x89, 0x277d =>   0x8a, 0x277e =>   0x8b, 0x277f =>   0x8c,
          0x2780 =>   0x8d, 0x2781 =>   0x8e, 0x2782 =>   0x8f, 0x2783 =>   0x90,
          0x2784 =>   0x91, 0x2785 =>   0x92, 0x2786 =>   0x93, 0x2787 =>   0x94,
          0x2788 =>   0x95, 0x2789 =>   0x96, 0x278a =>   0x97, 0x278b =>   0x98,
          0x278c =>   0x99, 0x278d =>   0x9a, 0x278e =>   0x9b, 0x278f =>   0x9c,
          0x2790 =>   0x9d, 0x2791 =>   0x9e, 0x2792 =>   0x9f, 0x2793 =>   0xa0,
          0x2794 =>   0xa1, 0x2192 =>   0xa2, 0x2194 =>   0xa3, 0x2195 =>   0xa4,
          0x2798 =>   0xa5, 0x2799 =>   0xa6, 0x279a =>   0xa7, 0x279b =>   0xa8,
          0x279c =>   0xa9, 0x279d =>   0xaa, 0x279e =>   0xab, 0x279f =>   0xac,
          0x27a0 =>   0xad, 0x27a1 =>   0xae, 0x27a2 =>   0xaf, 0x27a3 =>   0xb0,
          0x27a4 =>   0xb1, 0x27a5 =>   0xb2, 0x27a6 =>   0xb3, 0x27a7 =>   0xb4,
          0x27a8 =>   0xb5, 0x27a9 =>   0xb6, 0x27aa =>   0xb7, 0x27ab =>   0xb8,
          0x27ac =>   0xb9, 0x27ad =>   0xba, 0x27ae =>   0xbb, 0x27af =>   0xbc,
          0x27b1 =>   0xbd, 0x27b2 =>   0xbe, 0x27b3 =>   0xbf, 0x27b4 =>   0xc0,
          0x27b5 =>   0xc1, 0x27b6 =>   0xc2, 0x27b7 =>   0xc3, 0x27b8 =>   0xc4,
          0x27b9 =>   0xc5, 0x27ba =>   0xc6, 0x27bb =>   0xc7, 0x27bc =>   0xc8,
          0x27bd =>   0xc9, 0x27be =>   0xca);
        #require_once 'Zend/Pdf/Cmap.php';
        $this->_cmap = Zend_Pdf_Cmap::cmapWithTypeData(
            Zend_Pdf_Cmap::TYPE_BYTE_ENCODING_STATIC, $cmapData);


        /* Resource dictionary */

        /* The resource dictionary for the standard fonts is sparse because PDF
         * viewers already have all of the metrics data. We only need to provide
         * the font name and encoding method.
         */
        $this->_resource->BaseFont = new Zend_Pdf_Element_Name('ZapfDingbats');

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
