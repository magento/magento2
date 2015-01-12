<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\SalesRule\Block\Adminhtml\Promo\Widget;

class Chooser extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\SalesRule\Model\RuleFactory
     */
    protected $ruleFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\SalesRule\Model\RuleFactory $ruleFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\SalesRule\Model\RuleFactory $ruleFactory,
        array $data = []
    ) {
        $this->ruleFactory = $ruleFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Block constructor, prepare grid params
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setDefaultSort('rule_id');
        $this->setDefaultDir('ASC');
        $this->setUseAjax(true);
    }

    /**
     * Prepare rules collection
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        $collection = $this->ruleFactory->create()->getResourceCollection();
        $this->setCollection($collection);

        $this->_eventManager->dispatch(
            'adminhtml_block_promo_widget_chooser_prepare_collection',
            ['collection' => $collection]
        );

        return parent::_prepareCollection();
    }

    /**
     * Prepare chooser element HTML
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element Form Element
     * @return \Magento\Framework\Data\Form\Element\AbstractElement
     */
    public function prepareElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $uniqId = $this->mathRandom->getUniqueHash($element->getId());
        $sourceUrl = $this->getUrl('sales_rule/promo_quote/chooser', ['uniq_id' => $uniqId]);

        $chooser = $this->getLayout()->createBlock(
            'Magento\Widget\Block\Adminhtml\Widget\Chooser'
        )->setElement(
            $element
        )->setConfig(
            $this->getConfig()
        )->setFieldsetId(
            $this->getFieldsetId()
        )->setSourceUrl(
            $sourceUrl
        )->setUniqId(
            $uniqId
        );

        if ($element->getValue()) {
            $rule = $this->ruleFactory->create()->load((int)$element->getValue());
            if ($rule->getId()) {
                $chooser->setLabel($rule->getName());
            }
        }

        $element->setData('after_element_html', $chooser->toHtml());
        return $element;
    }

    /**
     * Grid Row JS Callback
     *
     * @return string
     */
    public function getRowClickCallback()
    {
        $chooserJsObject = $this->getId();
        $js = '
            function (grid, event) {
                var trElement = Event.findElement(event, "tr");
                var ruleName = trElement.down("td").next().innerHTML;
                var ruleId = trElement.down("td").innerHTML.replace(/^\s+|\s+$/g,"");
                ' .
            $chooserJsObject .
            '.setElementValue(ruleId);
                ' .
            $chooserJsObject .
            '.setElementLabel(ruleName);
                ' .
            $chooserJsObject .
            '.close();
            }
        ';
        return $js;
    }

    /**
     * Prepare columns for rules grid
     *
     * @return $this
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'rule_id',
            ['header' => __('ID'), 'align' => 'right', 'width' => '50px', 'index' => 'rule_id']
        );

        $this->addColumn('name', ['header' => __('Rule'), 'align' => 'left', 'index' => 'name']);

        $this->addColumn(
            'coupon_code',
            ['header' => __('Coupon Code'), 'align' => 'left', 'width' => '150px', 'index' => 'code']
        );

        $this->addColumn(
            'from_date',
            [
                'header' => __('Start on'),
                'align' => 'left',
                'width' => '120px',
                'type' => 'date',
                'index' => 'from_date'
            ]
        );

        $this->addColumn(
            'to_date',
            [
                'header' => __('End on'),
                'align' => 'left',
                'width' => '120px',
                'type' => 'date',
                'default' => '--',
                'index' => 'to_date'
            ]
        );

        $this->addColumn(
            'is_active',
            [
                'header' => __('Status'),
                'align' => 'left',
                'width' => '80px',
                'index' => 'is_active',
                'type' => 'options',
                'options' => [1 => 'Active', 0 => 'Inactive']
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * Prepare grid URL
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('sales_rule/promo_quote/chooser', ['_current' => true]);
    }
}
