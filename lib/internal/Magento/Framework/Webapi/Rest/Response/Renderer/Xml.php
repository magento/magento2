<?php
/**
 *  XML Renderer allows to format array or object as valid XML document.
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Webapi\Rest\Response\Renderer;

/**
 * Class \Magento\Framework\Webapi\Rest\Response\Renderer\Xml
 *
 * @since 2.0.0
 */
class Xml implements \Magento\Framework\Webapi\Rest\Response\RendererInterface
{
    /**
     * Renderer mime type.
     */
    const MIME_TYPE = 'application/xml';

    /**
     * Root node in XML output.
     */
    const XML_ROOT_NODE = 'response';

    /**
     * This value is used to replace numeric keys while formatting data for XML output.
     */
    const DEFAULT_ENTITY_ITEM_NAME = 'item';

    /**
     * @var \Magento\Framework\Xml\Generator
     * @since 2.0.0
     */
    protected $_xmlGenerator;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\Xml\Generator $xmlGenerator
     * @since 2.0.0
     */
    public function __construct(\Magento\Framework\Xml\Generator $xmlGenerator)
    {
        $this->_xmlGenerator = $xmlGenerator;
    }

    /**
     * Get XML renderer MIME type.
     *
     * @return string
     * @since 2.0.0
     */
    public function getMimeType()
    {
        return self::MIME_TYPE;
    }

    /**
     * Format object|array to valid XML.
     *
     * @param object|array|int|string|bool|float|null $data
     * @return string
     * @since 2.0.0
     */
    public function render($data)
    {
        $formattedData = $this->_formatData($data, true);
        /** Wrap response in a single node. */
        $formattedData = [self::XML_ROOT_NODE => $formattedData];
        $this->_xmlGenerator->setIndexedArrayItemName(self::DEFAULT_ENTITY_ITEM_NAME)->arrayToXml($formattedData);
        return $this->_xmlGenerator->getDom()->saveXML();
    }

    /**
     * Reformat mixed data to multidimensional array.
     *
     * This method is recursive.
     *
     * @param array|\Magento\Framework\DataObject $data
     * @param bool $isRoot
     * @return array
     * @throws \InvalidArgumentException
     * @since 2.0.0
     */
    protected function _formatData($data, $isRoot = false)
    {
        if (!is_array($data) && !is_object($data)) {
            if ($isRoot) {
                return $this->_formatValue($data);
            }
        } elseif ($data instanceof \Magento\Framework\DataObject) {
            $data = $data->toArray();
        } else {
            $data = (array)$data;
        }
        $isAssoc = !preg_match('/^\d+$/', implode(array_keys($data), ''));

        $formattedData = [];
        foreach ($data as $key => $value) {
            $value = is_array($value) || is_object($value) ? $this->_formatData($value) : $this->_formatValue($value);
            if ($isAssoc) {
                $formattedData[$this->_prepareKey($key)] = $value;
            } else {
                $formattedData[] = $value;
            }
        }
        return $formattedData;
    }

    /**
     * Prepare value in contrast with key.
     *
     * @param string $value
     * @return string
     * @since 2.0.0
     */
    protected function _formatValue($value)
    {
        if (is_bool($value)) {
            /** Without the following transformation boolean values are rendered incorrectly */
            $value = $value ? 'true' : 'false';
        }
        $replacementMap = ['&' => '&amp;'];
        return str_replace(array_keys($replacementMap), array_values($replacementMap), $value);
    }

    /**
     * Format array key or field name to be valid array key name.
     *
     * Replaces characters that are invalid in array key names.
     *
     * @param string $key
     * @return string
     * @since 2.0.0
     */
    protected function _prepareKey($key)
    {
        $replacementMap = [
            '!' => '',
            '"' => '',
            '#' => '',
            '$' => '',
            '%' => '',
            '&' => '',
            '\'' => '',
            '(' => '',
            ')' => '',
            '*' => '',
            '+' => '',
            ',' => '',
            '/' => '',
            ';' => '',
            '<' => '',
            '=' => '',
            '>' => '',
            '?' => '',
            '@' => '',
            '[' => '',
            '\\' => '',
            ']' => '',
            '^' => '',
            '`' => '',
            '{' => '',
            '|' => '',
            '}' => '',
            '~' => '',
            ' ' => '_',
            ':' => '_',
        ];
        $key = str_replace(array_keys($replacementMap), array_values($replacementMap), $key);
        $key = trim($key, '_');
        $prohibitedTagPattern = '/^[0-9,.-]/';
        if (preg_match($prohibitedTagPattern, $key)) {
            $key = self::DEFAULT_ENTITY_ITEM_NAME . '_' . $key;
        }
        return $key;
    }
}
