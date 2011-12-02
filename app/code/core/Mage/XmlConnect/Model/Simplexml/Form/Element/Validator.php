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
 * @package     Mage_XmlConnect
 * @copyright   Copyright (c) 2011 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Xmlconnect form validators container element
 *
 * @category    Mage
 * @package     Mage_XmlConnect
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_XmlConnect_Model_Simplexml_Form_Element_Validator
    extends Mage_XmlConnect_Model_Simplexml_Form_Element_Fieldset
{
    /**
     * Main element node
     *
     * @var string
     */
    protected $_mainNode = 'validators';

    /**
     * Validator id prefix
     *
     * @var string
     */
    protected $_validatorIdPrefix = 'validator_';

    /**
     * Rule type block renderer
     *
     * @var string
     */
    protected $_ruleTypeBlock = 'validator_rule';

    /**
     * Init validator container
     *
     * @param array $attributes
     */
    public function __construct($attributes = array())
    {
        parent::__construct($attributes);
        $this->_renderer = Mage_XmlConnect_Model_Simplexml_Form::getValidatorRenderer();
        $this->setType('validator');
    }

    /**
     * Skip name attribute for validator
     *
     * @todo re-factor required attributes logic to make it easy to replace them
     * @param Mage_XmlConnect_Model_Simplexml_Element $xmlObj
     * @return Mage_XmlConnect_Model_Simplexml_Form_Abstract
     */
    protected function _addName(Mage_XmlConnect_Model_Simplexml_Element $xmlObj)
    {
        return $this;
    }

    /**
     * Default element attribute array
     *
     * @return array
     */
    public function getXmlAttributes()
    {
        return array();
    }

    /**
     * Required element attribute array
     *
     * @return array
     */
    public function getRequiredXmlAttributes()
    {
        return array();
    }

    /**
     * Set element id
     *
     * @param $id
     * @return Mage_XmlConnect_Model_Simplexml_Form_Abstract
     */
    public function setId($id)
    {
        parent::setId($this->getValidatorIdPrefix() . $id);
        return $this;
    }

    /**
     * Get validator prefix
     *
     * @return string
     */
    public function getValidatorIdPrefix()
    {
        return $this->_validatorIdPrefix;
    }

    /**
     * Set validator prefix
     *
     * @param string $validatorIdPrefix
     * @return Mage_XmlConnect_Model_Simplexml_Form_Element_Validator
     */
    public function setValidatorIdPrefix($validatorIdPrefix)
    {
        $this->_validatorIdPrefix = $validatorIdPrefix;
        return $this;
    }

    /**
     * Get object attributes array
     *
     * @return array
     */
    public function getAttributes()
    {
        $attributes = array_merge($this->getXmlAttributes(), $this->getCustomAttributes());
        if (!empty($attributes)) {
            return $this->getXmlObjAttributes($attributes);
        } else {
            return $attributes;
        }
    }

    /**
     * Add rule element to validator container
     *
     * @param array $config
     * @param boolean $after
     * @return Mage_XmlConnect_Model_Simplexml_Form_Element_Abstract
     */
    public function addRule(array $config, $after = false)
    {
        if (isset($config['type'])) {
            $ruleType = $config['type'];
        }

        $elementId = $this->getXmlId() . '_' . $ruleType;
        $element = parent::addField($elementId, $this->getRuleTypeBlock(), $config, $after);
        if ($renderer = Mage_XmlConnect_Model_Simplexml_Form::getValidatorRuleRenderer()) {
            $element->setRenderer($renderer);
        }
        return $element;
    }

    /**
     * Get rule type block renderer
     *
     * @return string
     */
    public function getRuleTypeBlock()
    {
        return $this->_ruleTypeBlock;
    }

    /**
     * Set rule type block renderer
     *
     * @param string $ruleTypeBlock
     * @return Mage_XmlConnect_Model_Simplexml_Form_Element_Validator
     */
    public function setRuleTypeBlock($ruleTypeBlock)
    {
        $this->_ruleTypeBlock = $ruleTypeBlock;
        return $this;
    }
}
