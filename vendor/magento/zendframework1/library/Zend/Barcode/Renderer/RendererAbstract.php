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
 * @package    Zend_Barcode
 * @subpackage Renderer
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * Class for rendering the barcode
 *
 * @category   Zend
 * @package    Zend_Barcode
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_Barcode_Renderer_RendererAbstract
{
    /**
     * Namespace of the renderer for autoloading
     * @var string
     */
    protected $_rendererNamespace = 'Zend_Barcode_Renderer';

    /**
     * Renderer type
     * @var string
     */
    protected $_type = null;

    /**
     * Activate/Deactivate the automatic rendering of exception
     * @var boolean
     */
    protected $_automaticRenderError = false;

    /**
     * Offset of the barcode from the top of the rendering resource
     * @var integer
     */
    protected $_topOffset = 0;

    /**
     * Offset of the barcode from the left of the rendering resource
     * @var integer
     */
    protected $_leftOffset = 0;

    /**
     * Horizontal position of the barcode in the rendering resource
     * @var integer
     */
    protected $_horizontalPosition = 'left';

    /**
     * Vertical position of the barcode in the rendering resource
     * @var integer
     */
    protected $_verticalPosition = 'top';

    /**
     * Module size rendering
     * @var float
     */
    protected $_moduleSize = 1;

    /**
     * Barcode object
     * @var Zend_Barcode_Object_ObjectAbstract
     */
    protected $_barcode;

    /**
     * Drawing resource
     */
    protected $_resource;

    /**
     * Constructor
     *
     * @param array|Zend_Config $options
     * @return Zend_Barcode_Renderer_RendererAbstract
     */
    public function __construct($options = null)
    {
        if ($options instanceof Zend_Config) {
            $options = $options->toArray();
        }
        if (is_array($options)) {
            $this->setOptions($options);
        }
        $this->_type = strtolower(substr(
            get_class($this),
            strlen($this->_rendererNamespace) + 1
        ));
    }

    /**
     * Set renderer state from options array
     * @param  array $options
     * @return Zend_Renderer_Object
     */
    public function setOptions($options)
    {
        foreach ($options as $key => $value) {
            $method = 'set' . $key;
            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }
        return $this;
    }

    /**
     * Set renderer state from config object
     * @param Zend_Config $config
     * @return Zend_Renderer_Object
     */
    public function setConfig(Zend_Config $config)
    {
        return $this->setOptions($config->toArray());
    }

    /**
     * Set renderer namespace for autoloading
     *
     * @param string $namespace
     * @return Zend_Renderer_Object
     */
    public function setRendererNamespace($namespace)
    {
        $this->_rendererNamespace = $namespace;
        return $this;
    }

    /**
     * Retrieve renderer namespace
     *
     * @return string
     */
    public function getRendererNamespace()
    {
        return $this->_rendererNamespace;
    }

    /**
     * Retrieve renderer type
     * @return string
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * Manually adjust top position
     * @param integer $value
     * @return Zend_Barcode_Renderer
     * @throws Zend_Barcode_Renderer_Exception
     */
    public function setTopOffset($value)
    {
        if (!is_numeric($value) || intval($value) < 0) {
            #require_once 'Zend/Barcode/Renderer/Exception.php';
            throw new Zend_Barcode_Renderer_Exception(
                'Vertical position must be greater than or equals 0'
            );
        }
        $this->_topOffset = intval($value);
        return $this;
    }

    /**
     * Retrieve vertical adjustment
     * @return integer
     */
    public function getTopOffset()
    {
        return $this->_topOffset;
    }

    /**
     * Manually adjust left position
     * @param integer $value
     * @return Zend_Barcode_Renderer
     * @throws Zend_Barcode_Renderer_Exception
     */
    public function setLeftOffset($value)
    {
        if (!is_numeric($value) || intval($value) < 0) {
            #require_once 'Zend/Barcode/Renderer/Exception.php';
            throw new Zend_Barcode_Renderer_Exception(
                'Horizontal position must be greater than or equals 0'
            );
        }
        $this->_leftOffset = intval($value);
        return $this;
    }

    /**
     * Retrieve vertical adjustment
     * @return integer
     */
    public function getLeftOffset()
    {
        return $this->_leftOffset;
    }

    /**
     * Activate/Deactivate the automatic rendering of exception
     *
     * @param boolean $value
     * @return $this
     */
    public function setAutomaticRenderError($value)
    {
        $this->_automaticRenderError = (bool) $value;
        return $this;
    }

    /**
     * Horizontal position of the barcode in the rendering resource
     *
     * @param string $value
     * @return Zend_Barcode_Renderer
     * @throws Zend_Barcode_Renderer_Exception
     */
    public function setHorizontalPosition($value)
    {
        if (!in_array($value, array('left' , 'center' , 'right'))) {
            #require_once 'Zend/Barcode/Renderer/Exception.php';
            throw new Zend_Barcode_Renderer_Exception(
                "Invalid barcode position provided must be 'left', 'center' or 'right'"
            );
        }
        $this->_horizontalPosition = $value;
        return $this;
    }

    /**
     * Horizontal position of the barcode in the rendering resource
     * @return string
     */
    public function getHorizontalPosition()
    {
        return $this->_horizontalPosition;
    }

    /**
     * Vertical position of the barcode in the rendering resource
     *
     * @param string $value
     * @return self
     * @throws Zend_Barcode_Renderer_Exception
     */
    public function setVerticalPosition($value)
    {
        if (!in_array($value, array('top' , 'middle' , 'bottom'))) {
            #require_once 'Zend/Barcode/Renderer/Exception.php';
            throw new Zend_Barcode_Renderer_Exception(
                "Invalid barcode position provided must be 'top', 'middle' or 'bottom'"
            );
        }
        $this->_verticalPosition = $value;
        return $this;
    }

    /**
     * Vertical position of the barcode in the rendering resource
     * @return string
     */
    public function getVerticalPosition()
    {
        return $this->_verticalPosition;
    }

    /**
     * Set the size of a module
     * @param float $value
     * @return Zend_Barcode_Renderer
     * @throws Zend_Barcode_Renderer_Exception
     */
    public function setModuleSize($value)
    {
        if (!is_numeric($value) || floatval($value) <= 0) {
            #require_once 'Zend/Barcode/Renderer/Exception.php';
            throw new Zend_Barcode_Renderer_Exception(
                'Float size must be greater than 0'
            );
        }
        $this->_moduleSize = floatval($value);
        return $this;
    }


    /**
     * Set the size of a module
     * @return float
     */
    public function getModuleSize()
    {
        return $this->_moduleSize;
    }

    /**
     * Retrieve the automatic rendering of exception
     * @return boolean
     */
    public function getAutomaticRenderError()
    {
        return $this->_automaticRenderError;
    }

    /**
     * Set the barcode object
     *
     * @param Zend_Barcode_Object $barcode
     * @return Zend_Barcode_Renderer
     * @throws Zend_Barcode_Renderer_Exception
     */
    public function setBarcode($barcode)
    {
        if (!$barcode instanceof Zend_Barcode_Object_ObjectAbstract) {
            #require_once 'Zend/Barcode/Renderer/Exception.php';
            throw new Zend_Barcode_Renderer_Exception(
                'Invalid barcode object provided to setBarcode()'
            );
        }
        $this->_barcode = $barcode;
        return $this;
    }

    /**
     * Retrieve the barcode object
     * @return Zend_Barcode_Object
     */
    public function getBarcode()
    {
        return $this->_barcode;
    }

    /**
     * Checking of parameters after all settings
     * @return boolean
     */
    public function checkParams()
    {
        $this->_checkBarcodeObject();
        $this->_checkParams();
        return true;
    }

    /**
     * Check if a barcode object is correctly provided
     * @return void
     * @throws Zend_Barcode_Renderer_Exception
     */
    protected function _checkBarcodeObject()
    {
        if ($this->_barcode === null) {
            /**
             * @see Zend_Barcode_Renderer_Exception
             */
            #require_once 'Zend/Barcode/Renderer/Exception.php';
            throw new Zend_Barcode_Renderer_Exception(
                'No barcode object provided'
            );
        }
    }

    /**
     * Calculate the left and top offset of the barcode in the
     * rendering support
     *
     * @param float $supportHeight
     * @param float $supportWidth
     * @return void
     */
    protected function _adjustPosition($supportHeight, $supportWidth)
    {
        $barcodeHeight = $this->_barcode->getHeight(true) * $this->_moduleSize;
        if ($barcodeHeight != $supportHeight && $this->_topOffset == 0) {
            switch ($this->_verticalPosition) {
                case 'middle':
                    $this->_topOffset = floor(
                            ($supportHeight - $barcodeHeight) / 2);
                    break;
                case 'bottom':
                    $this->_topOffset = $supportHeight - $barcodeHeight;
                    break;
                case 'top':
                default:
                    $this->_topOffset = 0;
                    break;
            }
        }
        $barcodeWidth = $this->_barcode->getWidth(true) * $this->_moduleSize;
        if ($barcodeWidth != $supportWidth && $this->_leftOffset == 0) {
            switch ($this->_horizontalPosition) {
                case 'center':
                    $this->_leftOffset = floor(
                            ($supportWidth - $barcodeWidth) / 2);
                    break;
                case 'right':
                    $this->_leftOffset = $supportWidth - $barcodeWidth;
                    break;
                case 'left':
                default:
                    $this->_leftOffset = 0;
                    break;
            }
        }
    }

    /**
     * Draw the barcode in the rendering resource
     *
     * @return mixed
     * @throws Zend_Exception
     * @throws Zend_Barcode_Exception
     */
    public function draw()
    {
        try {
            $this->checkParams();
            $this->_initRenderer();
            $this->_drawInstructionList();
        } catch (Zend_Exception $e) {
            $renderable = false;
            if ($e instanceof Zend_Barcode_Exception) {
                $renderable = $e->isRenderable();
            }
            if ($this->_automaticRenderError && $renderable) {
                $barcode = Zend_Barcode::makeBarcode(
                    'error',
                    array('text' => $e->getMessage())
                );
                $this->setBarcode($barcode);
                $this->_resource = null;
                $this->_initRenderer();
                $this->_drawInstructionList();
            } else {
                if ($e instanceof Zend_Barcode_Exception) {
                    $e->setIsRenderable(false);
                }
                throw $e;
            }
        }
        return $this->_resource;
    }

    /**
     * Sub process to draw the barcode instructions
     * Needed by the automatic error rendering
     */
    private function _drawInstructionList()
    {
        $instructionList = $this->_barcode->draw();
        foreach ($instructionList as $instruction) {
            switch ($instruction['type']) {
                case 'polygon':
                    $this->_drawPolygon(
                        $instruction['points'],
                        $instruction['color'],
                        $instruction['filled']
                    );
                    break;
                case 'text': //$text, $size, $position, $font, $color, $alignment = 'center', $orientation = 0)
                    $this->_drawText(
                        $instruction['text'],
                        $instruction['size'],
                        $instruction['position'],
                        $instruction['font'],
                        $instruction['color'],
                        $instruction['alignment'],
                        $instruction['orientation']
                    );
                    break;
                default:
                    /**
                     * @see Zend_Barcode_Renderer_Exception
                     */
                    #require_once 'Zend/Barcode/Renderer/Exception.php';
                    throw new Zend_Barcode_Renderer_Exception(
                        'Unknown drawing command'
                    );
            }
        }
    }

    /**
     * Checking of parameters after all settings
     * @return void
     */
    abstract protected function _checkParams();

    /**
     * Render the resource by sending headers and drawed resource
     * @return mixed
     */
    abstract public function render();

    /**
     * Initialize the rendering resource
     * @return void
     */
    abstract protected function _initRenderer();

    /**
     * Draw a polygon in the rendering resource
     * @param array $points
     * @param integer $color
     * @param boolean $filled
     */
    abstract protected function _drawPolygon($points, $color, $filled = true);

    /**
     * Draw a polygon in the rendering resource
     *
     * @param string    $text
     * @param float     $size
     * @param array     $position
     * @param string    $font
     * @param integer   $color
     * @param string    $alignment
     * @param float|int $orientation
     */
    abstract protected function _drawText(
        $text,
        $size,
        $position,
        $font,
        $color,
        $alignment = 'center',
        $orientation = 0
    );
}
