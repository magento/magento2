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
 * @package     Mage_Rule
 * @copyright   Copyright (c) 2012 Magento Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Quote rule action abstract
 *
 * @category   Mage
 * @package    Mage_Rule
 * @author      Magento Core Team <core@magentocommerce.com>
 */
abstract class Mage_Rule_Model_Action_Abstract extends Varien_Object implements Mage_Rule_Model_Action_Interface
{
    public function __construct()
    {
        parent::__construct();
        $this->loadAttributeOptions()->loadOperatorOptions()->loadValueOptions();

        foreach ($this->getAttributeOption() as $attr=>$dummy) { $this->setAttribute($attr); break; }
        foreach ($this->getOperatorOption() as $operator=>$dummy) { $this->setOperator($operator); break; }
    }

    public function getForm()
    {
        return $this->getRule()->getForm();
    }

    public function asArray(array $arrAttributes = array())
    {
        $out = array(
            'type'=>$this->getType(),
            'attribute'=>$this->getAttribute(),
            'operator'=>$this->getOperator(),
            'value'=>$this->getValue(),
        );
        return $out;
    }

    public function asXml()
    {
        $xml = "<type>".$this->getType()."</type>"
            ."<attribute>".$this->getAttribute()."</attribute>"
            ."<operator>".$this->getOperator()."</operator>"
            ."<value>".$this->getValue()."</value>";
        return $xml;
    }

    public function loadArray(array $arr)
    {
        $this->addData(array(
            'type'=>$arr['type'],
            'attribute'=>$arr['attribute'],
            'operator'=>$arr['operator'],
            'value'=>$arr['value'],
        ));
        $this->loadAttributeOptions();
        $this->loadOperatorOptions();
        $this->loadValueOptions();
        return $this;
    }

    public function loadAttributeOptions()
    {
        $this->setAttributeOption(array());
        return $this;
    }

    public function getAttributeSelectOptions()
    {
        $opt = array();
        foreach ($this->getAttributeOption() as $k=>$v) {
            $opt[] = array('value'=>$k, 'label'=>$v);
        }
        return $opt;
    }

    public function getAttributeName()
    {
        return $this->getAttributeOption($this->getAttribute());
    }

    public function loadOperatorOptions()
    {
        $this->setOperatorOption(array(
            '=' => Mage::helper('Mage_Rule_Helper_Data')->__('to'),
            '+=' => Mage::helper('Mage_Rule_Helper_Data')->__('by'),
        ));
        return $this;
    }

    public function getOperatorSelectOptions()
    {
        $opt = array();
        foreach ($this->getOperatorOption() as $k=>$v) {
            $opt[] = array('value'=>$k, 'label'=>$v);
        }
        return $opt;
    }

    public function getOperatorName()
    {
        return $this->getOperatorOption($this->getOperator());
    }

    public function loadValueOptions()
    {
        $this->setValueOption(array());
        return $this;
    }

    public function getValueSelectOptions()
    {
        $opt = array();
        foreach ($this->getValueOption() as $k=>$v) {
            $opt[] = array('value'=>$k, 'label'=>$v);
        }
        return $opt;
    }

    public function getValueName()
    {
        $value = $this->getValue();
        return !empty($value) || 0===$value ? $value : '...';;
    }

    public function getNewChildSelectOptions()
    {
        return array(
            array('value'=>'', 'label'=>Mage::helper('Mage_Rule_Helper_Data')->__('Please choose an action to add...')),
        );
    }

    public function getNewChildName()
    {
        return $this->getAddLinkHtml();
    }

    public function asHtml()
    {
        return '';
    }

    public function asHtmlRecursive()
    {
        $str = $this->asHtml();
        return $str;
    }

    public function getTypeElement()
    {
        return $this->getForm()->addField('action:'.$this->getId().':type', 'hidden', array(
            'name'=>'rule[actions]['.$this->getId().'][type]',
            'value'=>$this->getType(),
            'no_span'=>true,
        ));
    }

    public function getAttributeElement()
    {
        return $this->getForm()->addField('action:'.$this->getId().':attribute', 'select', array(
            'name'=>'rule[actions]['.$this->getId().'][attribute]',
            'values'=>$this->getAttributeSelectOptions(),
            'value'=>$this->getAttribute(),
            'value_name'=>$this->getAttributeName(),
        ))->setRenderer(Mage::getBlockSingleton('Mage_Rule_Block_Editable'));
    }

    public function getOperatorElement()
    {
        return $this->getForm()->addField('action:'.$this->getId().':operator', 'select', array(
            'name'=>'rule[actions]['.$this->getId().'][operator]',
            'values'=>$this->getOperatorSelectOptions(),
            'value'=>$this->getOperator(),
            'value_name'=>$this->getOperatorName(),
        ))->setRenderer(Mage::getBlockSingleton('Mage_Rule_Block_Editable'));
    }

    public function getValueElement()
    {
        return $this->getForm()->addField('action:'.$this->getId().':value', 'text', array(
            'name'=>'rule[actions]['.$this->getId().'][value]',
            'value'=>$this->getValue(),
            'value_name'=>$this->getValueName(),
        ))->setRenderer(Mage::getBlockSingleton('Mage_Rule_Block_Editable'));
    }

    public function getAddLinkHtml()
    {
        $src = Mage::getDesign()->getViewFileUrl('images/rule_component_add.gif');
        $html = '<img src="'.$src.'" alt="" class="rule-param-add v-middle" />';
        return $html;
    }


    public function getRemoveLinkHtml()
    {
        $src = Mage::getDesign()->getViewFileUrl('images/rule_component_remove.gif');
        $html = '<span class="rule-param"><a href="javascript:void(0)" class="rule-param-remove"><img src="'
            . $src . '" alt="" class="v-middle" /></a></span>';
        return $html;
    }

    public function asString($format='')
    {
        return "";
    }

    public function asStringRecursive($level=0)
    {
        $str = str_pad('', $level*3, ' ', STR_PAD_LEFT).$this->asString();
        return $str;
    }

    public function process()
    {
        return $this;
    }
}
