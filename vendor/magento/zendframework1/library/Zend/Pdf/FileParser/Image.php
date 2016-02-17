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
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */


/** Internally used classes */

/** Zend_Pdf_Image */
#require_once 'Zend/Pdf/Image.php';


/** Zend_Pdf_FileParser */
#require_once 'Zend/Pdf/FileParser.php';

/**
 * FileParser for Zend_Pdf_Image subclasses.
 *
 * @package    Zend_Pdf
 * @subpackage FileParser
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Pdf_FileParser_Image extends Zend_Pdf_FileParser
{
    /**
     * Image Type
     *
     * @var integer
     */
    protected $imageType;

    /**
     * Object constructor.
     *
     * Validates the data source and enables debug logging if so configured.
     *
     * @param Zend_Pdf_FileParserDataSource $dataSource
     * @throws Zend_Pdf_Exception
     */
    public function __construct(Zend_Pdf_FileParserDataSource $dataSource)
    {
        parent::__construct($dataSource);
        $this->imageType = Zend_Pdf_Image::TYPE_UNKNOWN;
    }
}
