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
 * @version    $Id: Image.php 20096 2010-01-06 02:05:09Z bkarwin $
 */


/** Internally used classes */
#require_once 'Zend/Pdf/Element/Object.php';
#require_once 'Zend/Pdf/Element/Dictionary.php';
#require_once 'Zend/Pdf/Element/Name.php';
#require_once 'Zend/Pdf/Element/Numeric.php';


/** Zend_Pdf_Resource */
#require_once 'Zend/Pdf/Resource.php';


/**
 * Content stream (drawing instructions container)
 *
 * @package    Zend_Pdf
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Pdf_Resource_ContentStream extends Zend_Pdf_Resource
{
    /**
     * Buffered content
     *
     * @var string
     */
    protected $_bufferedContent = '';

    /**
     * Object constructor.
     *
     * @param Zend_Pdf_Element_Object_Stream|string $contentStreamObject
     * @throws Zend_Pdf_Exception
     */
    public function __construct($contentStreamObject = '')
    {
        if ($contentStreamObject !== null &&
            !$contentStreamObject instanceof Zend_Pdf_Element_Object_Stream &&
            !is_string($contentStreamObject)
        ) {
            #require_once 'Zend/Pdf/Exception.php';
            throw new Zend_Pdf_Exception('Content stream parameter must be a string or stream object');
        }

        parent::__construct($contentStreamObject);
    }

    /**
     * Appends instructions to the end of the content stream
     *
     * @param string $instructions
     * @return Zend_Pdf_Resource_ContentStream
     */
    public function addInstructions($instructions)
    {
        $this->_bufferedContent .= $instructions;
        return $this;
    }

    /**
     * Get current stream content
     *
     * @return string
     */
    public function getInstructions()
    {
        $this->flush();
        return $this->_resource->value;
    }

    /**
     * Clear stream content.
     *
     * @return Zend_Pdf_Resource_ContentStream
     */
    public function clear()
    {
        $this->_resource->value = '';
        $this->_bufferedContent = '';
        return $this;
    }

    /**
     * Flush buffered content
     */
    public function flush()
    {
        $this->_resource->value .= $this->_bufferedContent;
        $this->_bufferedContent = '';

        return $this;
    }
}
