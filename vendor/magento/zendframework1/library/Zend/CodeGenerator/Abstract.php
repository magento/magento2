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
 * @package    Zend_CodeGenerator
 * @subpackage PHP
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @category   Zend
 * @package    Zend_CodeGenerator
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 */
abstract class Zend_CodeGenerator_Abstract
{

    /**
     * @var string
     */
    protected $_sourceContent = null;

    /**
     * @var bool
     */
    protected $_isSourceDirty = true;

    /**
     * __construct()
     *
     * @param array $options
     */
    public function __construct($options = array())
    {
        $this->_init();
        if ($options != null) {
            // use Zend_Config objects if provided
            if ($options instanceof Zend_Config) {
                $options = $options->toArray();
            }
            // pass arrays to setOptions
            if (is_array($options)) {
                $this->setOptions($options);
            }
        }
        $this->_prepare();
    }

    /**
     * setConfig()
     *
     * @param Zend_Config $config
     * @return Zend_CodeGenerator_Abstract
     */
    public function setConfig(Zend_Config $config)
    {
        $this->setOptions($config->toArray());
        return $this;
    }

    /**
     * setOptions()
     *
     * @param array $options
     * @return Zend_CodeGenerator_Abstract
     */
    public function setOptions(Array $options)
    {
        foreach ($options as $optionName => $optionValue) {
            $methodName = 'set' . $optionName;
            if (method_exists($this, $methodName)) {
                $this->{$methodName}($optionValue);
            }
        }
        return $this;
    }

    /**
     * setSourceContent()
     *
     * @param string $sourceContent
     */
    public function setSourceContent($sourceContent)
    {
        $this->_sourceContent = $sourceContent;
        return;
    }

    /**
     * getSourceContent()
     *
     * @return string
     */
    public function getSourceContent()
    {
        return $this->_sourceContent;
    }

    /**
     * _init() - this is called before the constuctor
     *
     */
    protected function _init()
    {

    }

    /**
     * _prepare() - this is called at construction completion
     *
     */
    protected function _prepare()
    {

    }

    /**
     * generate() - must be implemented by the child
     *
     */
    abstract public function generate();

    /**
     * __toString() - casting to a string will in turn call generate()
     *
     * @return string
     */
    final public function __toString()
    {
        return $this->generate();
    }

}
