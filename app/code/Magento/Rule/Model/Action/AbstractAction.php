<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Rule\Model\Action;

use Magento\Framework\Data\Form;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Abstract rule action
 *
 * @api
 * @since 2.0.0
 */
abstract class AbstractAction extends \Magento\Framework\DataObject implements ActionInterface
{
    /**
     * @var \Magento\Framework\View\Asset\Repository
     * @since 2.0.0
     */
    protected $_assetRepo;

    /**
     * @var \Magento\Framework\View\LayoutInterface
     * @since 2.0.0
     */
    protected $_layout;

    /**
     * Base name for hidden elements
     * @var string
     * @since 2.0.0
     */
    protected $elementName = 'rule';

    /**
     * @param \Magento\Framework\View\Asset\Repository $assetRepo
     * @param \Magento\Framework\View\LayoutInterface $layout
     * @param array $data
     * @since 2.0.0
     */
    public function __construct(
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Magento\Framework\View\LayoutInterface $layout,
        array $data = []
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
     * @since 2.0.0
     */
    public function getForm()
    {
        return $this->getRule()->getForm();
    }

    /**
     * @param array $arrAttributes
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function asArray(array $arrAttributes = [])
    {
        $out = [
            'type' => $this->getType(),
            'attribute' => $this->getAttribute(),
            'operator' => $this->getOperator(),
            'value' => $this->getValue(),
        ];
        return $out;
    }

    /**
     * @return string
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function loadArray(array $arr)
    {
        $this->addData(
            [
                'type' => $arr['type'],
                'attribute' => $arr['attribute'],
                'operator' => $arr['operator'],
                'value' => $arr['value'],
            ]
        );
        $this->loadAttributeOptions();
        $this->loadOperatorOptions();
        $this->loadValueOptions();
        return $this;
    }

    /**
     * @return $this
     * @since 2.0.0
     */
    public function loadAttributeOptions()
    {
        $this->setAttributeOption([]);
        return $this;
    }

    /**
     * @return array
     * @since 2.0.0
     */
    public function getAttributeSelectOptions()
    {
        $opt = [];
        foreach ($this->getAttributeOption() as $key => $value) {
            $opt[] = ['value' => $key, 'label' => $value];
        }
        return $opt;
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getAttributeName()
    {
        return $this->getAttributeOption($this->getAttribute());
    }

    /**
     * @return $this
     * @since 2.0.0
     */
    public function loadOperatorOptions()
    {
        $this->setOperatorOption(['=' => __('to'), '+=' => __('by')]);
        return $this;
    }

    /**
     * @return array
     * @since 2.0.0
     */
    public function getOperatorSelectOptions()
    {
        $opt = [];
        foreach ($this->getOperatorOption() as $k => $v) {
            $opt[] = ['value' => $k, 'label' => $v];
        }
        return $opt;
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getOperatorName()
    {
        return $this->getOperatorOption($this->getOperator());
    }

    /**
     * @return $this
     * @since 2.0.0
     */
    public function loadValueOptions()
    {
        $this->setValueOption([]);
        return $this;
    }

    /**
     * @return array
     * @since 2.0.0
     */
    public function getValueSelectOptions()
    {
        $opt = [];
        foreach ($this->getValueOption() as $key => $value) {
            $opt[] = ['value' => $key, 'label' => $value];
        }
        return $opt;
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getValueName()
    {
        $value = $this->getValue();
        return !empty($value) || 0 === $value ? $value : '...';
    }

    /**
     * @return array
     * @since 2.0.0
     */
    public function getNewChildSelectOptions()
    {
        return [['value' => '', 'label' => __('Please choose an action to add.')]];
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getNewChildName()
    {
        return $this->getAddLinkHtml();
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function asHtml()
    {
        return '';
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function asHtmlRecursive()
    {
        $str = $this->asHtml();
        return $str;
    }

    /**
     * @return AbstractElement
     * @since 2.0.0
     */
    public function getTypeElement()
    {
        return $this->getForm()->addField(
            'action:' . $this->getId() . ':type',
            'hidden',
            [
                'name' => $this->elementName . '[actions][' . $this->getId() . '][type]',
                'value' => $this->getType(),
                'no_span' => true
            ]
        );
    }

    /**
     * @return $this
     * @since 2.0.0
     */
    public function getAttributeElement()
    {
        return $this->getForm()->addField(
            'action:' . $this->getId() . ':attribute',
            'select',
            [
                'name' => $this->elementName . '[actions][' . $this->getId() . '][attribute]',
                'values' => $this->getAttributeSelectOptions(),
                'value' => $this->getAttribute(),
                'value_name' => $this->getAttributeName()
            ]
        )->setRenderer(
            $this->_layout->getBlockSingleton(\Magento\Rule\Block\Editable::class)
        );
    }

    /**
     * @return $this
     * @since 2.0.0
     */
    public function getOperatorElement()
    {
        return $this->getForm()->addField(
            'action:' . $this->getId() . ':operator',
            'select',
            [
                'name' => $this->elementName . '[actions][' . $this->getId() . '][operator]',
                'values' => $this->getOperatorSelectOptions(),
                'value' => $this->getOperator(),
                'value_name' => $this->getOperatorName()
            ]
        )->setRenderer(
            $this->_layout->getBlockSingleton(\Magento\Rule\Block\Editable::class)
        );
    }

    /**
     * @return $this
     * @since 2.0.0
     */
    public function getValueElement()
    {
        return $this->getForm()->addField(
            'action:' . $this->getId() . ':value',
            'text',
            [
                'name' => $this->elementName . '[actions][' . $this->getId() . '][value]',
                'value' => $this->getValue(),
                'value_name' => $this->getValueName()
            ]
        )->setRenderer(
            $this->_layout->getBlockSingleton(\Magento\Rule\Block\Editable::class)
        );
    }

    /**
     * @return string
     * @since 2.0.0
     */
    public function getAddLinkHtml()
    {
        $src = $this->_assetRepo->getUrl('images/rule_component_add.gif');
        $html = '<img src="' . $src . '" alt="" class="rule-param-add v-middle" />';
        return $html;
    }

    /**
     * @return string
     * @since 2.0.0
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
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @since 2.0.0
     */
    public function asString($format = '')
    {
        return "";
    }

    /**
     * @param int $level
     * @return string
     * @since 2.0.0
     */
    public function asStringRecursive($level = 0)
    {
        $str = str_pad('', $level * 3, ' ', STR_PAD_LEFT) . $this->asString();
        return $str;
    }

    /**
     * @return $this
     * @since 2.0.0
     */
    public function process()
    {
        return $this;
    }
}
