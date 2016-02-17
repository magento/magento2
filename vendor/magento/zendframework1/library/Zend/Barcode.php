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
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * Class for generate Barcode
 *
 * @category   Zend
 * @package    Zend_Barcode
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
class Zend_Barcode
{
    /**
     * Factory for Zend_Barcode classes.
     *
     * First argument may be a string containing the base of the adapter class
     * name, e.g. 'int25' corresponds to class Zend_Barcode_Object_Int25.  This
     * is case-insensitive.
     *
     * First argument may alternatively be an object of type Zend_Config.
     * The barcode class base name is read from the 'barcode' property.
     * The barcode config parameters are read from the 'params' property.
     *
     * Second argument is optional and may be an associative array of key-value
     * pairs.  This is used as the argument to the barcode constructor.
     *
     * If the first argument is of type Zend_Config, it is assumed to contain
     * all parameters, and the second argument is ignored.
     *
     * @param  mixed $barcode         String name of barcode class, or Zend_Config object.
     * @param  mixed $renderer        String name of renderer class
     * @param  mixed $barcodeConfig   OPTIONAL; an array or Zend_Config object with barcode parameters.
     * @param  mixed $rendererConfig  OPTIONAL; an array or Zend_Config object with renderer parameters.
     * @param  boolean $automaticRenderError  OPTIONAL; set the automatic rendering of exception
     * @return Zend_Barcode
     * @throws Zend_Barcode_Exception
     */
    public static function factory(
        $barcode,
        $renderer = 'image',
        $barcodeConfig = array(),
        $rendererConfig = array(),
        $automaticRenderError = true
    ) {
        /*
         * Convert Zend_Config argument to plain string
         * barcode name and separate config object.
         */
        if ($barcode instanceof Zend_Config) {
            if (isset($barcode->rendererParams)) {
                $rendererConfig = $barcode->rendererParams->toArray();
            }
            if (isset($barcode->renderer)) {
                $renderer = (string) $barcode->renderer;
            }
            if (isset($barcode->barcodeParams)) {
                $barcodeConfig = $barcode->barcodeParams->toArray();
            }
            if (isset($barcode->barcode)) {
                $barcode = (string) $barcode->barcode;
            } else {
                $barcode = null;
            }
        }

        try {
            $barcode  = self::makeBarcode($barcode, $barcodeConfig);
            $renderer = self::makeRenderer($renderer, $rendererConfig);
        } catch (Zend_Exception $e) {
            $renderable = ($e instanceof Zend_Barcode_Exception) ? $e->isRenderable() : false;
            if ($automaticRenderError && $renderable) {
                $barcode = self::makeBarcode('error', array(
                    'text' => $e->getMessage()
                ));
                $renderer = self::makeRenderer($renderer, array());
            } else {
                throw $e;
            }
        }

        $renderer->setAutomaticRenderError($automaticRenderError);
        return $renderer->setBarcode($barcode);
    }

    /**
     * Barcode Constructor
     *
     * @param mixed $barcode        String name of barcode class, or Zend_Config object.
     * @param mixed $barcodeConfig  OPTIONAL; an array or Zend_Config object with barcode parameters.
     * @return Zend_Barcode_Object
     */
    public static function makeBarcode($barcode, $barcodeConfig = array())
    {
        if ($barcode instanceof Zend_Barcode_Object_ObjectAbstract) {
            return $barcode;
        }

        /*
         * Convert Zend_Config argument to plain string
         * barcode name and separate config object.
         */
        if ($barcode instanceof Zend_Config) {
            if (isset($barcode->barcodeParams) && $barcode->barcodeParams instanceof Zend_Config) {
                $barcodeConfig = $barcode->barcodeParams->toArray();
            }
            if (isset($barcode->barcode)) {
                $barcode = (string) $barcode->barcode;
            } else {
                $barcode = null;
            }
        }
        if ($barcodeConfig instanceof Zend_Config) {
            $barcodeConfig = $barcodeConfig->toArray();
        }

        /*
         * Verify that barcode parameters are in an array.
         */
        if (!is_array($barcodeConfig)) {
            /**
             * @see Zend_Barcode_Exception
             */
            #require_once 'Zend/Barcode/Exception.php';
            throw new Zend_Barcode_Exception(
                'Barcode parameters must be in an array or a Zend_Config object'
            );
        }

        /*
         * Verify that an barcode name has been specified.
         */
        if (!is_string($barcode) || empty($barcode)) {
            /**
             * @see Zend_Barcode_Exception
             */
            #require_once 'Zend/Barcode/Exception.php';
            throw new Zend_Barcode_Exception(
                'Barcode name must be specified in a string'
            );
        }
        /*
         * Form full barcode class name
         */
        $barcodeNamespace = 'Zend_Barcode_Object';
        if (isset($barcodeConfig['barcodeNamespace'])) {
            $barcodeNamespace = $barcodeConfig['barcodeNamespace'];
        }

        $barcodeName = strtolower($barcodeNamespace . '_' . $barcode);
        $barcodeName = str_replace(' ', '_', ucwords(
            str_replace( '_', ' ', $barcodeName)
        ));

        /*
         * Load the barcode class.  This throws an exception
         * if the specified class cannot be loaded.
         */
        if (!class_exists($barcodeName)) {
            #require_once 'Zend/Loader.php';
            Zend_Loader::loadClass($barcodeName);
        }

        /*
         * Create an instance of the barcode class.
         * Pass the config to the barcode class constructor.
         */
        $bcAdapter = new $barcodeName($barcodeConfig);

        /*
         * Verify that the object created is a descendent of the abstract barcode type.
         */
        if (!$bcAdapter instanceof Zend_Barcode_Object_ObjectAbstract) {
            /**
             * @see Zend_Barcode_Exception
             */
            #require_once 'Zend/Barcode/Exception.php';
            throw new Zend_Barcode_Exception(
                "Barcode class '$barcodeName' does not extend Zend_Barcode_Object_ObjectAbstract"
            );
        }
        return $bcAdapter;
    }

    /**
     * Renderer Constructor
     *
     * @param mixed $renderer           String name of renderer class, or Zend_Config object.
     * @param mixed $rendererConfig     OPTIONAL; an array or Zend_Config object with renderer parameters.
     * @return Zend_Barcode_Renderer
     */
    public static function makeRenderer($renderer = 'image', $rendererConfig = array())
    {
        if ($renderer instanceof Zend_Barcode_Renderer_RendererAbstract) {
            return $renderer;
        }

        /*
         * Convert Zend_Config argument to plain string
         * barcode name and separate config object.
         */
        if ($renderer instanceof Zend_Config) {
            if (isset($renderer->rendererParams)) {
                $rendererConfig = $renderer->rendererParams->toArray();
            }
            if (isset($renderer->renderer)) {
                $renderer = (string) $renderer->renderer;
            }
        }
        if ($rendererConfig instanceof Zend_Config) {
            $rendererConfig = $rendererConfig->toArray();
        }

        /*
         * Verify that barcode parameters are in an array.
         */
        if (!is_array($rendererConfig)) {
            /**
             * @see Zend_Barcode_Exception
             */
            #require_once 'Zend/Barcode/Exception.php';
            $e = new Zend_Barcode_Exception(
                'Barcode parameters must be in an array or a Zend_Config object'
            );
            $e->setIsRenderable(false);
            throw $e;
        }

        /*
         * Verify that an barcode name has been specified.
         */
        if (!is_string($renderer) || empty($renderer)) {
            /**
             * @see Zend_Barcode_Exception
             */
            #require_once 'Zend/Barcode/Exception.php';
            $e = new Zend_Barcode_Exception(
                'Renderer name must be specified in a string'
            );
            $e->setIsRenderable(false);
            throw $e;
        }

        /*
         * Form full barcode class name
         */
        $rendererNamespace = 'Zend_Barcode_Renderer';
        if (isset($rendererConfig['rendererNamespace'])) {
            $rendererNamespace = $rendererConfig['rendererNamespace'];
        }

        $rendererName = strtolower($rendererNamespace . '_' . $renderer);
        $rendererName = str_replace(' ', '_', ucwords(
            str_replace( '_', ' ', $rendererName)
        ));

        /*
         * Load the barcode class.  This throws an exception
         * if the specified class cannot be loaded.
         */
        if (!class_exists($rendererName)) {
            #require_once 'Zend/Loader.php';
            Zend_Loader::loadClass($rendererName);
        }

        /*
         * Create an instance of the barcode class.
         * Pass the config to the barcode class constructor.
         */
        $rdrAdapter = new $rendererName($rendererConfig);

        /*
         * Verify that the object created is a descendent of the abstract barcode type.
         */
        if (!$rdrAdapter instanceof Zend_Barcode_Renderer_RendererAbstract) {
            /**
             * @see Zend_Barcode_Exception
             */
            #require_once 'Zend/Barcode/Exception.php';
            $e = new Zend_Barcode_Exception(
                "Renderer class '$rendererName' does not extend Zend_Barcode_Renderer_RendererAbstract"
            );
            $e->setIsRenderable(false);
            throw $e;
        }
        return $rdrAdapter;
    }

    /**
     * Proxy to renderer render() method
     *
     * @param string | Zend_Barcode_Object | array | Zend_Config $barcode
     * @param string | Zend_Barcode_Renderer $renderer
     * @param array | Zend_Config $barcodeConfig
     * @param array | Zend_Config $rendererConfig
     */
    public static function render(
        $barcode,
        $renderer,
        $barcodeConfig = array(),
        $rendererConfig = array()
    ) {
        self::factory($barcode, $renderer, $barcodeConfig, $rendererConfig)->render();
    }

    /**
     * Proxy to renderer draw() method
     *
     * @param string | Zend_Barcode_Object | array | Zend_Config $barcode
     * @param string | Zend_Barcode_Renderer $renderer
     * @param array | Zend_Config $barcodeConfig
     * @param array | Zend_Config $rendererConfig
     * @return mixed
     */
    public static function draw(
        $barcode,
        $renderer,
        $barcodeConfig = array(),
        $rendererConfig = array()
    ) {
        return self::factory($barcode, $renderer, $barcodeConfig, $rendererConfig)->draw();
    }

    /**
     * Proxy for setBarcodeFont of Zend_Barcode_Object
     * @param string $font
     * @eturn void
     */
    public static function setBarcodeFont($font)
    {
        #require_once 'Zend/Barcode/Object/ObjectAbstract.php';
        Zend_Barcode_Object_ObjectAbstract::setBarcodeFont($font);
    }
}
