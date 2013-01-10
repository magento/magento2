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
 * @package     Mage_Core
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

class Mage_Core_Model_Layout_Translator
{
    /**
     * @var Mage_Core_Model_Factory_Helper
     */
    protected $_helperFactory;

    /**
     * @param array $data
     * @throws InvalidArgumentException
     */
    public function __construct(array $data = array())
    {
        $this->_helperFactory = isset($data['helperRegistry']) ?
            $data['helperRegistry'] :
            Mage::getSingleton('Mage_Core_Model_Factory_Helper');

        if (false === ($this->_helperFactory instanceof Mage_Core_Model_Factory_Helper)) {
            throw new InvalidArgumentException(
                'helperFactory object has to instance of Mage_Core_Model_Factory_Helper'
            );
        }
    }

    /**
     * Translate layout node
     *
     * @param Varien_Simplexml_Element $node
     * @param array $args
     **/
    public function translateActionParameters(Varien_Simplexml_Element $node, &$args)
    {
        if (false === $this->_isNodeTranslatable($node)) {
            return;
        }
        $moduleName = $this->_getTranslateModuleName($node);

        foreach ($this->_getNodeNamesToTranslate($node) as $translatableArg) {
            /*
             * .(dot) character is used as a path separator in nodes hierarchy
             * e.g. info.title means that Magento needs to translate value of <title> node
             * that is a child of <info> node
             */
            // @var $argumentHierarchy array - path to translatable item in $args array
            $argumentHierarchy = explode('.', $translatableArg);
            $argumentStack = &$args;
            $canTranslate = true;
            while (is_array($argumentStack) && count($argumentStack) > 0) {
                $argumentName = array_shift($argumentHierarchy);
                if (isset($argumentStack[$argumentName])) {
                    /*
                     * Move to the next element in arguments hierarchy
                     * in order to find target translatable argument
                     */
                    $argumentStack = &$argumentStack[$argumentName];
                } else {
                    // Target argument cannot be found
                    $canTranslate = false;
                    break;
                }
            }
            if ($canTranslate && is_string($argumentStack)) {
                // $argumentStack is now a reference to target translatable argument so it can be translated
                $argumentStack = $this->_translateValue($argumentStack, $moduleName);
            }
        }
    }

    /**
     * Translate argument value
     *
     * @param Varien_Simplexml_Element $node
     * @param string|null $moduleName
     * @return string
     */
    public function translateArgument(Varien_Simplexml_Element $node, $moduleName = null)
    {
        $moduleName = $this->_getTranslateModuleName($node, $moduleName);
        $value = $this->_getNodeValue($node);

        if ($this->_isSelfTranslatable($node)) {
            $value = $this->_translateValue($value, $moduleName);
        } elseif ($this->_isNodeTranslatable($node->getParent())) {
            if (true === in_array($node->getName(), $this->_getNodeNamesToTranslate($node->getParent()))) {
                $value = $this->_translateValue($value, $moduleName);
            }
        }

        return $value;
    }

    /**
     * Get node names that have to be translated
     *
     * @param $node
     * @return array
     */
    protected function _getNodeNamesToTranslate(Varien_Simplexml_Element $node)
    {
        return explode(' ', (string)$node['translate']);
    }

    /**
     * Check if node has to be translated
     *
     * @param Varien_Simplexml_Element $node
     * @return bool
     */
    protected function _isNodeTranslatable(Varien_Simplexml_Element $node)
    {
        return isset($node['translate']);
    }

    /**
     * Get translate module name
     *
     * @param Varien_Simplexml_Element $node
     * @param string|null $defaultModule
     * @return string
     */
    protected function _getTranslateModuleName(Varien_Simplexml_Element $node, $defaultModule = null)
    {
        return isset($node['module']) ?
            (string)$node['module'] :
            (empty($defaultModule) ? 'Mage_Core' : $defaultModule);
    }

    /**
     * Check if node has to translate own value
     *
     * @param Varien_Simplexml_Element $node
     * @return bool
     */
    protected function _isSelfTranslatable(Varien_Simplexml_Element $node)
    {
        return $this->_isNodeTranslatable($node) && 'true' == (string)$node['translate'];
    }

    /**
     * Get node value
     *
     * @param Varien_Simplexml_Element $node
     * @return string
     */
    protected function _getNodeValue(Varien_Simplexml_Element $node)
    {
        return trim((string)$node);
    }

    /**
     * Translate node value
     *
     * @param string $value
     * @param string $moduleName
     * @return string
     */
    protected function _translateValue($value, $moduleName)
    {
        return $this->_helperFactory->get($moduleName)->__($value);
    }
}
