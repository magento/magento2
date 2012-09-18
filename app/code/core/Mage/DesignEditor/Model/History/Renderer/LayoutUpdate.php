<?php
/**
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
 * @category    Mage
 * @package     Mage_DesignEditor
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * History output renderer to get layout update
 */
class Mage_DesignEditor_Model_History_Renderer_LayoutUpdate implements Mage_DesignEditor_Model_History_RendererInterface
{
    /**
     * Get Layout update out of collection of changes
     *
     * @param Mage_DesignEditor_Model_Change_Collection $collection
     * @return string
     */
    public function render(Mage_DesignEditor_Model_Change_Collection $collection)
    {
        $dom = new DOMDocument();
        $dom->formatOutput = true;
        $dom->loadXML($this->_getInitialXml());

        foreach ($collection as $item) {
            if ($item instanceof Mage_DesignEditor_Model_Change_LayoutAbstract) {
                $this->_render($dom, $item);
            }
        }

        $layoutUpdate = $dom->saveXML();

        return $layoutUpdate;
    }

    /**
     * Get initial XML structure
     *
     * @return string
     */
    protected function _getInitialXml()
    {
        return '<?xml version="1.0" encoding="UTF-8"?><layout></layout>';
    }

    /**
     * Render layout update for single layout change
     *
     * @param DOMDocument $dom
     * @param Mage_DesignEditor_Model_Change_LayoutAbstract $item
     * @return DOMElement
     */
    protected function _render(DOMDocument $dom, $item)
    {
        $handle = $this->_getHandleNode($dom, $item->getData('handle'));

        $directive = $dom->createElement($item->getLayoutDirective());
        $handle->appendChild($directive);

        foreach ($item->getLayoutUpdateData() as $attribute => $value) {
            $directive->setAttribute($attribute, $value);
        }
        return $handle;
    }

    /**
     * Create or get existing handle node
     *
     * @param DOMDocument $dom
     * @param string $handle
     * @return DOMElement
     */
    protected function _getHandleNode($dom, $handle)
    {
        $node = $dom->getElementsByTagName($handle)->item(0);
        if (!$node) {
            $node = $dom->createElement($handle);
            $dom->documentElement->appendChild($node);
        }

        return $node;
    }
}
