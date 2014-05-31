<?php
/**
 * Initial configuration data converter. Converts \DOMDocument to array
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
namespace Magento\Framework\App\Config\Initial;

class Converter implements \Magento\Framework\Config\ConverterInterface
{
    /**
     * Node paths to process
     *
     * @var array
     */
    protected $_nodeMap = array();

    /**
     * @var array
     */
    protected $_metadata = array();

    /**
     * @param array $nodeMap
     */
    public function __construct(array $nodeMap = array())
    {
        $this->_nodeMap = $nodeMap;
    }

    /**
     * Convert config data
     *
     * @param \DOMDocument $source
     * @return array
     */
    public function convert($source)
    {
        $output = array();
        $xpath = new \DOMXPath($source);
        $this->_metadata = array();

        /** @var $node \DOMNode */
        foreach ($xpath->query(implode(' | ', $this->_nodeMap)) as $node) {
            $output = array_merge($output, $this->_convertNode($node));
        }
        return array('data' => $output, 'metadata' => $this->_metadata);
    }

    /**
     * Convert node oto array
     *
     * @param \DOMNode $node
     * @param string $path
     * @return array|string|null
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function _convertNode(\DOMNode $node, $path = '')
    {
        $output = array();
        if ($node->nodeType == XML_ELEMENT_NODE) {
            if ($node->hasAttributes()) {
                $backendModel = $node->attributes->getNamedItem('backend_model');
                if ($backendModel) {
                    $this->_metadata[$path] = array('backendModel' => $backendModel->nodeValue);
                }
            }
            $nodeData = array();
            /** @var $childNode \DOMNode */
            foreach ($node->childNodes as $childNode) {
                $childrenData = $this->_convertNode($childNode, ($path ? $path . '/' : '') . $childNode->nodeName);
                if ($childrenData == null) {
                    continue;
                }
                if (is_array($childrenData)) {
                    $nodeData = array_merge($nodeData, $childrenData);
                } else {
                    $nodeData = $childrenData;
                }
            }
            if (is_array($nodeData) && empty($nodeData)) {
                $nodeData = null;
            }
            $output[$node->nodeName] = $nodeData;
        } elseif ($node->nodeType == XML_CDATA_SECTION_NODE || $node->nodeType == XML_TEXT_NODE && trim(
            $node->nodeValue
        ) != ''
        ) {
            return $node->nodeValue;
        }

        return $output;
    }
}
