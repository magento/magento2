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
 * @version    $Id: Style.php 20096 2010-01-06 02:05:09Z bkarwin $
 */

#require_once 'Zend/Pdf/Canvas/Abstract.php';

/**
 * Canvas is an abstract rectangle drawing area which can be dropped into
 * page object at specified place.
 *
 * @package    Zend_Pdf
 * @copyright  Copyright (c) 2005-2010 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Pdf_Canvas extends Zend_Pdf_Canvas_Abstract
{
    /**
     * Canvas procedure sets.
     *
     * Allowed values: 'PDF', 'Text', 'ImageB', 'ImageC', 'ImageI'.
     *
     * @var array
     */
    protected $_procSet = array();

    /**
     * Canvas width expressed in default user space units (1/72 inch)
     *
     * @var float
     */
    protected $_width;

    /**
     * Canvas height expressed in default user space units (1/72 inch)
     *
     * @var float
     */
    protected $_height;

    protected $_resources = array('Font'      => array(),
                                  'XObject'   => array(),
                                  'ExtGState' => array());

    /**
     * Object constructor
     *
     * @param float $width
     * @param float $height
     */
    public function __construct($width, $height)
    {
        $this->_width  = $width;
        $this->_height = $height;
    }

    /**
     * Add procedure set to the canvas description
     *
     * @param string $procSetName
     */
    protected function _addProcSet($procSetName)
    {
        $this->_procset[$procSetName] = 1;
    }

    /**
     * Attach resource to the canvas
     *
     * Method returns a name of the resource which can be used
     * as a resource reference within drawing instructions stream
     * Allowed types: 'ExtGState', 'ColorSpace', 'Pattern', 'Shading',
     * 'XObject', 'Font', 'Properties'
     *
     * @param string $type
     * @param Zend_Pdf_Resource $resource
     * @return string
     */
    protected function _attachResource($type, Zend_Pdf_Resource $resource)
    {
        // Check, that resource is already attached to resource set.
        $resObject = $resource->getResource();
        foreach ($this->_resources[$type] as $resName => $collectedResObject) {
            if ($collectedResObject === $resObject) {
                return $resName;
            }
        }

        $idCounter = 1;
        do {
            $newResName = $type[0] . $idCounter++;
        } while (isset($this->_resources[$type][$newResName]));

        $this->_resources[$type][$newResName] = $resObject;

        return $newResName;
    }

    /**
     * Returns dictionaries of used resources.
     *
     * Used for canvas implementations interoperability
     *
     * Structure of the returned array:
     * array(
     *   <resTypeName> => array(
     *                      <resName> => <Zend_Pdf_Resource object>,
     *                      <resName> => <Zend_Pdf_Resource object>,
     *                      <resName> => <Zend_Pdf_Resource object>,
     *                      ...
     *                    ),
     *   <resTypeName> => array(
     *                      <resName> => <Zend_Pdf_Resource object>,
     *                      <resName> => <Zend_Pdf_Resource object>,
     *                      <resName> => <Zend_Pdf_Resource object>,
     *                      ...
     *                    ),
     *   ...
     *   'ProcSet' => array()
     * )
     *
     * where ProcSet array is a list of used procedure sets names (strings).
     * Allowed procedure set names: 'PDF', 'Text', 'ImageB', 'ImageC', 'ImageI'
     *
     * @internal
     * @return array
     */
    public function getResources()
    {
        $this->_resources['ProcSet'] = array_keys($this->_procSet);
        return $this->_resources;
    }

    /**
     * Get drawing instructions stream
     *
     * It has to be returned as a PDF stream object to make it reusable.
     *
     * @internal
     * @returns Zend_Pdf_Resource_ContentStream
     */
    public function getContents()
    {
        /** @todo implementation */
    }

    /**
     * Return the height of this page in points.
     *
     * @return float
     */
    public function getHeight()
    {
        return $this->_height;
    }

    /**
     * Return the width of this page in points.
     *
     * @return float
     */
    public function getWidth()
    {
        return $this->_width;
    }
}
