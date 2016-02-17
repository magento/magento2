<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/zf2 for the canonical source repository
 * @copyright Copyright (c) 2005-2015 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Zend\Log\Formatter;

use DateTime;
use DOMDocument;
use DOMElement;
use Traversable;
use Zend\Escaper\Escaper;
use Zend\Stdlib\ArrayUtils;

class Xml implements FormatterInterface
{
    /**
     * @var string Name of root element
     */
    protected $rootElement;

    /**
     * @var array Relates XML elements to log data field keys.
     */
    protected $elementMap;

    /**
     * @var string Encoding to use in XML
     */
    protected $encoding;

    /**
     * @var Escaper instance
     */
    protected $escaper;

    /**
     * Format specifier for DateTime objects in event data (default: ISO 8601)
     *
     * @see http://php.net/manual/en/function.date.php
     * @var string
     */
    protected $dateTimeFormat = self::DEFAULT_DATETIME_FORMAT;

    /**
     * Class constructor
     * (the default encoding is UTF-8)
     *
     * @param array|Traversable $options
     * @return Xml
     */
    public function __construct($options = array())
    {
        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        }

        if (!is_array($options)) {
            $args = func_get_args();

            $options = array(
                'rootElement' => array_shift($args)
            );

            if (count($args)) {
                $options['elementMap'] = array_shift($args);
            }

            if (count($args)) {
                $options['encoding'] = array_shift($args);
            }

            if (count($args)) {
                $options['dateTimeFormat'] = array_shift($args);
            }
        }

        if (!array_key_exists('rootElement', $options)) {
            $options['rootElement'] = 'logEntry';
        }

        if (!array_key_exists('encoding', $options)) {
            $options['encoding'] = 'UTF-8';
        }

        $this->rootElement = $options['rootElement'];
        $this->setEncoding($options['encoding']);

        if (array_key_exists('elementMap', $options)) {
            $this->elementMap  = $options['elementMap'];
        }

        if (array_key_exists('dateTimeFormat', $options)) {
            $this->setDateTimeFormat($options['dateTimeFormat']);
        }
    }

    /**
     * Get encoding
     *
     * @return string
     */
    public function getEncoding()
    {
        return $this->encoding;
    }

    /**
     * Set encoding
     *
     * @param string $value
     * @return Xml
     */
    public function setEncoding($value)
    {
        $this->encoding = (string) $value;
        return $this;
    }

    /**
     * Set Escaper instance
     *
     * @param  Escaper $escaper
     * @return Xml
     */
    public function setEscaper(Escaper $escaper)
    {
        $this->escaper = $escaper;
        return $this;
    }

    /**
     * Get Escaper instance
     *
     * Lazy-loads an instance with the current encoding if none registered.
     *
     * @return Escaper
     */
    public function getEscaper()
    {
        if (null === $this->escaper) {
            $this->setEscaper(new Escaper($this->getEncoding()));
        }
        return $this->escaper;
    }

    /**
     * Formats data into a single line to be written by the writer.
     *
     * @param array $event event data
     * @return string formatted line to write to the log
     */
    public function format($event)
    {
        if (isset($event['timestamp']) && $event['timestamp'] instanceof DateTime) {
            $event['timestamp'] = $event['timestamp']->format($this->getDateTimeFormat());
        }

        $dataToInsert = $event;

        if (null !== $this->elementMap) {
            $dataToInsert = array();

            foreach ($this->elementMap as $elementName => $fieldKey) {
                $dataToInsert[$elementName] = $event[$fieldKey];
            }
        }

        $enc     = $this->getEncoding();
        $escaper = $this->getEscaper();
        $dom     = new DOMDocument('1.0', $enc);
        $elt     = $dom->appendChild(new DOMElement($this->rootElement));

        foreach ($dataToInsert as $key => $value) {
            if (empty($value)
                || is_scalar($value)
                || ((is_array($value) || $value instanceof Traversable) && $key == "extra")
                || (is_object($value) && method_exists($value, '__toString'))
            ) {
                if ($key == "message") {
                    $value = $escaper->escapeHtml($value);
                }

                if ($key == "extra" && empty($value)) {
                    continue;
                }

                if ($key == "extra" && (is_array($value) || $value instanceof Traversable)) {
                    $elt->appendChild($this->buildElementTree($dom, $dom->createElement('extra'), $value));

                    continue;
                }

                $elt->appendChild(new DOMElement($key, (string) $value));
            }
        }

        return preg_replace('/<\?xml version="1.0"( encoding="[^\"]*")?\?>\n/u', '', $dom->saveXML()) . PHP_EOL;
    }

    /**
     * Recursion function to create an xml tree structure out of array structure
     * @param DomDocument $doc - DomDocument where the current nodes will be generated
     * @param DomElement $rootElement - root element the tree will be attached to
     * @param $mixedData array|Traversable - mixedData
     * @return DomElement $domElement - DOM Element with appended child nodes
     */
    protected function buildElementTree(DOMDocument $doc, DOMElement $rootElement, $mixedData)
    {
        if (! (is_array($mixedData) || $mixedData instanceof Traversable)) {
            return $rootElement;
        }

        foreach ($mixedData as $key => $value) {
            // key is numeric and switch is not possible, numeric values are not valid node names
            if ((empty($value) || is_numeric($value)) && is_numeric($key)) {
                continue;
            }

            if ($value instanceof Traversable || is_array($value)) {
                // current value is an array, start recursion
                $rootElement->appendChild($this->buildElementTree($doc, $doc->createElement($key), $value));

                continue;
            }

            if (is_object($value) && ! method_exists($value, '__toString')) {
                // object does not support __toString() method, manually convert the value
                $value = $this->getEscaper()->escapeHtml(
                    '"Object" of type ' . get_class($value) . " does not support __toString() method"
                );
            }

            if (is_numeric($key)) {
                // xml does not allow numeric values, try to switch the value and the key
                $key   = (string) $value;
                $value = null;
            }

            try {
                $rootElement->appendChild(new DOMElement($key, empty($value) ? null : (string) $value));
            } catch (\DOMException $e) {
                // the input name is not valid, go one.
                continue;
            }
        }

        return $rootElement;
    }

    /**
     * {@inheritDoc}
     */
    public function getDateTimeFormat()
    {
        return $this->dateTimeFormat;
    }

    /**
     * {@inheritDoc}
     */
    public function setDateTimeFormat($dateTimeFormat)
    {
        $this->dateTimeFormat = (string) $dateTimeFormat;
        return $this;
    }
}
