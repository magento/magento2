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


/**
 * Zend_Pdf_ImageFactory
 *
 * Helps manage the diverse set of supported image file types.
 *
 * @package    Zend_Pdf
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @todo       Use Zend_Mime not file extension for type determination.
 */
class Zend_Pdf_Resource_ImageFactory
{
    public static function factory($filename) {
        if(!is_file($filename)) {
            #require_once 'Zend/Pdf/Exception.php';
            throw new Zend_Pdf_Exception("Cannot create image resource. File not found.");
        }
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        /*
         * There are plans to use Zend_Mime and not file extension. In the mean time, if you need to
         * use an alternate file extension just spin up the right processor directly.
         */
        switch (strtolower($extension)) {
            case 'tif':
                //Fall through to next case;
            case 'tiff':
                #require_once 'Zend/Pdf/Resource/Image/Tiff.php';
                return new Zend_Pdf_Resource_Image_Tiff($filename);
                break;
            case 'png':
                #require_once 'Zend/Pdf/Resource/Image/Png.php';
                return new Zend_Pdf_Resource_Image_Png($filename);
                break;
            case 'jpg':
                //Fall through to next case;
            case 'jpe':
                //Fall through to next case;
            case 'jpeg':
                #require_once 'Zend/Pdf/Resource/Image/Jpeg.php';
                return new Zend_Pdf_Resource_Image_Jpeg($filename);
                break;
            default:
                #require_once 'Zend/Pdf/Exception.php';
                throw new Zend_Pdf_Exception("Cannot create image resource. File extension not known or unsupported type.");
                break;
        }
    }
}

