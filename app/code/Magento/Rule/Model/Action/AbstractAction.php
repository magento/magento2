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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Rule\Model\Action;

use Magento\Framework\Data\Form;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Quote rule action abstract
 */
abstract class AbstractAction extends \Magento\Framework\Object implements ActionInterface
{
    /**
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $_assetRepo;

    /**
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $_layout;

    /**
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\Framework\View\LayoutInterface $layout
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Framework\View\LayoutInterface $layout,
        array $data = array()
    ) {
        $this->_assetRepo = $assetRepo;
        $this->_layout = $layout;

        parent::__construct($data);

        $this->loadAttributeOptions()->loadOperatorOptions()->loadValueOptions();

        foreach (array_keys($this->getAttributeOption()) as $attr) {
            $this->setAttribute($attr);
            break;
        }
        foreach (array_keys($this->getOperatorOption()) as $operator) {
            $this->setOperator($operator);
            break;
        }
    }

    /**
     * @return Form
     */
    public function getForm()
    {
        return $this->getRule()->getForm();
    }

    /**
     * @param array $arrAttributes
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function asArray(array $arrAttributes = array())
    {
        $out = array(
            'type' => $this->getType(),
            'attribute' => $this->getAttribute(),
            'operator' => $this->getOperator(),
            'value' => $this->getValue()
        );
        return $out;
    }

    /**
     * @return string
     */
    public function asXml()
    {
        $xml = "<type>" .
            $this->getType() .
            "</type>" .
            "<attribute>" .
            $this->getAttribute() .
            "</attribute>" .
            "<operator>" .
            $this->getOperator() .
            "</operator>" .
            "<value>" .
            $this->getValue() .
            "</value>";
        return $xml;
    }

    /**
     * @param array $arr
     * @return $this
     */
    public function loadArray(array $arr)
    {
        $this->addData(
            array(
                'type' => $arr['type'],
                'attribute' => $arr['attribute'],
                'operator' => $arr['operator'],
                'value' => $arr['value']
            )
        );
        $this->loadAttributeOptions();
        $this->loadOperatorOptions();
        $this->loadValueOptions();
        return $this;
    }

    /**
     * @return $this
     */
    public function loadAttributeOptions()
    {
        $this->setAttributeOption(array());
        return $this;
    }

    /**
     * @return array
     */
    public function getAttributeSelectOptions()
    {
        $opt = array();
        foreach ($this->getAttributeOption() as $key => $value) {
            $opt[] = array('value' => $key, 'label' => $value);
        }
        return $opt;
    }

    /**
     * @return string
     */
    public function getAttributeName()
    {
        return $this->getAttributeOption($this->getAttribute());
    }

    /**
     * @return $this
     */
    public function loadOperatorOptions()
    {
        $this->setOperatorOption(array('=' => __('to'), '+=' => __('by')));
        return $this;
    }

    /**
     * @return array
     */
    public function getOperatorSelectOptions()
    {
        $opt = array();
        foreach ($this->getOperatorOption() as $k => $v) {
            $opt[] = array('value' => $k, 'label' => $v);
        }
        return $opt;
    }

    /**
     * @return string
     */
    public function getOperatorName()
    {
        return $this->getOperatorOption($this->getOperator());
    }

    /**
     * @return $this
     */
    public function loadValueOptions()
    {
        $this->setValueOption(array());
        return $this;
    }

    /**
     * @return array
     */
    public function getValueSelectOptions()
    {
        $opt = array();
        foreach ($this->getValueOption() as $key => $value) {
            $opt[] = array('value' => $key, 'label' => $value);
        }
        return $opt;
    }

    /**
     * @return string
     */
    public function getValueName()
    {
        $value = $this->getValue();
        return !empty($value) || 0 === $value ? $value : '...';
    }

    /**
     * @return array
     */
    public function getNewChildSelectOptions()
    {
        return array(array('value' => '', 'label' => __('Please choose an action to add.')));
    }

    /**
     * @return string
     */
    public function getNewChildName()
    {
        return $this->getAddLinkHtml();
    }

    /**
     * @return string
     */
    public function asHtml()
    {
        return '';
    }

    /**
     * @return string
     */
    public function asHtmlRecursive()
    {
        $str = $this->asHtml();
        return $str;
    }

    /**
     * @return AbstractElement
     */
    public function getTypeElement()
    {
        return $this->getForm()->addField(
            'action:' . $this->getId() . ':type',
            'hidden',
            array(
                'name' => 'rule[actions][' . $this->getId() . '][type]',
                'value' => $this->getType(),
                'no_span' => true
            )
        );
    }

    /**
     * @return $this
     */
    public function getAttributeElement()
    {
        return $this->getForm()->addField(
            'action:' . $this->getId() . ':attribute',
            'select',
            array(
                'name' => 'rule[actions][' . $this->getId() . '][attribute]',
                'values' => $this->getAttributeSelectOptions(),
                'value' => $this->getAttribute(),
                'value_name' => $this->getAttributeName()
            )
        )->setRenderer(
            $this->_layout->getBlockSingleton('Magento\Rule\Block\Editable')
        );
    }

    /**
     * @return $this
     */
    public function getOperatorElement()
    {
        return $this->getForm()->addField(
            'action:' . $this->getId() . ':operator',
            'select',
            array(
                'name' => 'rule[actions][' . $this->getId() . '][operator]',
                'values' => $this->getOperatorSelectOptions(),
                'value' => $this->getOperator(),
                'value_name' => $this->getOperatorName()
            )
        )->setRenderer(
            $this->_layout->getBlockSingleton('Magento\Rule\Block\Editable')
        );
    }

    /**
     * @return $this
     */
    public function getValueElement()
    {
        return $this->getForm()->addField(
            'action:' . $this->getId() . ':value',
            'text',
            array(
                'name' => 'rule[actions][' . $this->getId() . '][value]',
                'value' => $this->getValue(),
                'value_name' => $this->getValueName()
            )
        )->setRenderer(
            $this->_layout->getBlockSingleton('Magento\Rule\Block\Editable')
        );
    }

    /**
     * @return string
     */
    public function getAddLinkHtml()
    {
        $src = $this->_assetRepo->getUrl('images/rule_component_add.gif');
        $html = '<img src="' . $src . '" alt="" class="rule-param-add v-middle" />';
        return $html;
    }

    /**
     * @return string
     */
    public function getRemoveLinkHtml()
    {
        $src = $this->_assetRepo->getUrl('images/rule_component_remove.gif');
        $html = '<span class="rule-param"><a href="javascript:void(0)" class="rule-param-remove"><img src="' .
            $src .
            '" alt="" class="v-middle" /></a></span>';
        return $html;
    }

    /**
     * @param string $format
     * @return string
     */
    public function asString($format = '')
    {
        return "";
    }

    /**
     * @param int $level
     * @return string
     */
    public function asStringRecursive($level = 0)
    {
        $str = str_pad('', $level * 3, ' ', STR_PAD_LEFT) . $this->asString();
        return $str;
    }

    /**
     * @return $this
     */
    public function process()
    {
        return $this;
    }
}
