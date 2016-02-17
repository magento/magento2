<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Config\Reader;

use XMLReader;
use Zend\Config\Exception;

/**
 * XML config reader.
 */
class Xml implements ReaderInterface
{
    /**
     * XML Reader instance.
     *
     * @var XMLReader
     */
    protected $reader;

    /**
     * Directory of the file to process.
     *
     * @var string
     */
    protected $directory;

    /**
     * Nodes to handle as plain text.
     *
     * @var array
     */
    protected $textNodes = array(
        XMLReader::TEXT,
        XMLReader::CDATA,
        XMLReader::WHITESPACE,
        XMLReader::SIGNIFICANT_WHITESPACE
    );

    /**
     * fromFile(): defined by Reader interface.
     *
     * @see    ReaderInterface::fromFile()
     * @param  string $filename
     * @return array
     * @throws Exception\RuntimeException
     */
    public function fromFile($filename)
    {
        if (!is_file($filename) || !is_readable($filename)) {
            throw new Exception\RuntimeException(sprintf(
                "File '%s' doesn't exist or not readable",
                $filename
            ));
        }
        $this->reader = new XMLReader();
        $this->reader->open($filename, null, LIBXML_XINCLUDE);

        $this->directory = dirname($filename);

        set_error_handler(
            function ($error, $message = '') use ($filename) {
                throw new Exception\RuntimeException(
                    sprintf('Error reading XML file "%s": %s', $filename, $message),
                    $error
                );
            },
            E_WARNING
        );
        $return = $this->process();
        restore_error_handler();
        $this->reader->close();

        return $return;
    }

    /**
     * fromString(): defined by Reader interface.
     *
     * @see    ReaderInterface::fromString()
     * @param  string $string
     * @return array|bool
     * @throws Exception\RuntimeException
     */
    public function fromString($string)
    {
        if (empty($string)) {
            return array();
        }
        $this->reader = new XMLReader();

        $this->reader->xml($string, null, LIBXML_XINCLUDE);

        $this->directory = null;

        set_error_handler(
            function ($error, $message = '') {
                throw new Exception\RuntimeException(
                    sprintf('Error reading XML string: %s', $message),
                    $error
                );
            },
            E_WARNING
        );
        $return = $this->process();
        restore_error_handler();
        $this->reader->close();

        return $return;
    }

    /**
     * Process data from the created XMLReader.
     *
     * @return array
     */
    protected function process()
    {
        return $this->processNextElement();
    }

    /**
     * Process the next inner element.
     *
     * @return mixed
     */
    protected function processNextElement()
    {
        $children = array();
        $text     = '';

        while ($this->reader->read()) {
            if ($this->reader->nodeType === XMLReader::ELEMENT) {
                if ($this->reader->depth === 0) {
                    return $this->processNextElement();
                }

                $attributes = $this->getAttributes();
                $name       = $this->reader->name;

                if ($this->reader->isEmptyElement) {
                    $child = array();
                } else {
                    $child = $this->processNextElement();
                }

                if ($attributes) {
                    if (is_string($child)) {
                        $child = array('_' => $child);
                    }

                    if (! is_array($child)) {
                        $child = array();
                    }

                    $child = array_merge($child, $attributes);
                }

                if (isset($children[$name])) {
                    if (!is_array($children[$name]) || !array_key_exists(0, $children[$name])) {
                        $children[$name] = array($children[$name]);
                    }

                    $children[$name][] = $child;
                } else {
                    $children[$name] = $child;
                }
            } elseif ($this->reader->nodeType === XMLReader::END_ELEMENT) {
                break;
            } elseif (in_array($this->reader->nodeType, $this->textNodes)) {
                $text .= $this->reader->value;
            }
        }

        return $children ?: $text;
    }

    /**
     * Get all attributes on the current node.
     *
     * @return array
     */
    protected function getAttributes()
    {
        $attributes = array();

        if ($this->reader->hasAttributes) {
            while ($this->reader->moveToNextAttribute()) {
                $attributes[$this->reader->localName] = $this->reader->value;
            }

            $this->reader->moveToElement();
        }

        return $attributes;
    }
}
