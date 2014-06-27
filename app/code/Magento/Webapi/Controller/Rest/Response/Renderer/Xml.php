<?php
/**
 *  XML Renderer allows to format array or object as valid XML document.
 *
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Webapi\Controller\Rest\Response\Renderer;

class Xml implements \Magento\Webapi\Controller\Rest\Response\RendererInterface
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

    /** @var \Magento\Framework\Xml\Generator */
    protected $_xmlGenerator;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\Xml\Generator $xmlGenerator
     */
    public function __construct(\Magento\Framework\Xml\Generator $xmlGenerator)
    {
        $this->_xmlGenerator = $xmlGenerator;
    }

    /**
     * Get XML renderer MIME type.
     *
     * @return string
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
     */
    public function render($data)
    {
        $formattedData = $this->_formatData($data, true);
        /** Wrap response in a single node. */
        $formattedData = array(self::XML_ROOT_NODE => $formattedData);
        $this->_xmlGenerator->setIndexedArrayItemName(self::DEFAULT_ENTITY_ITEM_NAME)->arrayToXml($formattedData);
        return $this->_xmlGenerator->getDom()->saveXML();
    }

    /**
     * Reformat mixed data to multidimensional array.
     *
     * This method is recursive.
     *
     * @param array|\Magento\Framework\Object $data
     * @param bool $isRoot
     * @return array
     * @throws \InvalidArgumentException
     */
    protected function _formatData($data, $isRoot = false)
    {
        if (!is_array($data) && !is_object($data)) {
            if ($isRoot) {
                return $this->_formatValue($data);
            }
        } elseif ($data instanceof \Magento\Framework\Object) {
            $data = $data->toArray();
        } else {
            $data = (array)$data;
        }
        $isAssoc = !preg_match('/^\d+$/', implode(array_keys($data), ''));

        $formattedData = array();
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
     */
    protected function _formatValue($value)
    {
        if (is_bool($value)) {
            /** Without the following transformation boolean values are rendered incorrectly */
            $value = $value ? 'true' : 'false';
        }
        $replacementMap = array('&' => '&amp;');
        return str_replace(array_keys($replacementMap), array_values($replacementMap), $value);
    }

    /**
     * Format array key or field name to be valid array key name.
     *
     * Replaces characters that are invalid in array key names.
     *
     * @param string $key
     * @return string
     */
    protected function _prepareKey($key)
    {
        $replacementMap = array(
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
            ':' => '_'
        );
        $key = str_replace(array_keys($replacementMap), array_values($replacementMap), $key);
        $key = trim($key, '_');
        $prohibitedTagPattern = '/^[0-9,.-]/';
        if (preg_match($prohibitedTagPattern, $key)) {
            $key = self::DEFAULT_ENTITY_ITEM_NAME . '_' . $key;
        }
        return $key;
    }
}
