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
        array $data = array()
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
            array('collection' => $collection)
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
        $sourceUrl = $this->getUrl('sales_rule/promo_quote/chooser', array('uniq_id' => $uniqId));

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
            array('header' => __('ID'), 'align' => 'right', 'width' => '50px', 'index' => 'rule_id')
        );

        $this->addColumn('name', array('header' => __('Rule'), 'align' => 'left', 'index' => 'name'));

        $this->addColumn(
            'coupon_code',
            array('header' => __('Coupon Code'), 'align' => 'left', 'width' => '150px', 'index' => 'code')
        );

        $this->addColumn(
            'from_date',
            array(
                'header' => __('Start on'),
                'align' => 'left',
                'width' => '120px',
                'type' => 'date',
                'index' => 'from_date'
            )
        );

        $this->addColumn(
            'to_date',
            array(
                'header' => __('End on'),
                'align' => 'left',
                'width' => '120px',
                'type' => 'date',
                'default' => '--',
                'index' => 'to_date'
            )
        );

        $this->addColumn(
            'is_active',
            array(
                'header' => __('Status'),
                'align' => 'left',
                'width' => '80px',
                'index' => 'is_active',
                'type' => 'options',
                'options' => array(1 => 'Active', 0 => 'Inactive')
            )
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
        return $this->getUrl('sales_rule/promo_quote/chooser', array('_current' => true));
    }
}
