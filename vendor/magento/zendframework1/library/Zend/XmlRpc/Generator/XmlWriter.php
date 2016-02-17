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
 * @package    Zend_XmlRpc
 * @subpackage Generator
 * @copyright  Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license    http://framework.zend.com/license/new-bsd     New BSD License
 * @version    $Id$
 */

/**
 * @var Zend_XmlRpc_Generator_GeneratorAbstract
 */
#require_once 'Zend/XmlRpc/Generator/GeneratorAbstract.php';

/**
 * XML generator adapter based on XMLWriter
 */
class Zend_XmlRpc_Generator_XmlWriter extends Zend_XmlRpc_Generator_GeneratorAbstract
{
    /**
     * XMLWriter instance
     *
     * @var XMLWriter
     */
    protected $_xmlWriter;

    /**
     * Initialized XMLWriter instance
     *
     * @return void
     */
    protected function _init()
    {
        $this->_xmlWriter = new XMLWriter();
        $this->_xmlWriter->openMemory();
        $this->_xmlWriter->startDocument('1.0', $this->_encoding);
    }


    /**
     * Open a new XML element
     *
     * @param string $name XML element name
     * @return void
     */
    protected function _openElement($name)
    {
        $this->_xmlWriter->startElement($name);
    }

    /**
     * Write XML text data into the currently opened XML element
     *
     * @param string $text XML text data
     * @return void
     */
    protected function _writeTextData($text)
    {
        $this->_xmlWriter->text($text);
    }

    /**
     * Close an previously opened XML element
     *
     * @param string $name
     * @return void
     */
    protected function _closeElement($name)
    {
        $this->_xmlWriter->endElement();

        return $this;
    }

    public function saveXml()
    {
        $xml = $this->_xmlWriter->flush(false);
        return $xml;
    }
}
