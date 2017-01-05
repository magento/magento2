<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Sales widget search form for orders and returns block
 */
namespace Magento\Sales\Block\Widget\Guest;

use Magento\Customer\Model\Context;

class Form extends \Magento\Framework\View\Element\Template implements \Magento\Widget\Block\BlockInterface
{
    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\Http\Context $httpContext,
        array $data = []
    ) {
        $this->httpContext = $httpContext;
        parent::__construct($context, $data);
        $this->_isScopePrivate = true;
    }

    /**
     * Check whether module is available
     *
     * @return bool
     */
    public function isEnable()
    {
        return !($this->httpContext->getValue(Context::CONTEXT_AUTH));
    }

    /**
     * Select element for choosing registry type
     *
     * @return array
     */
    public function getTypeSelectHtml()
    {
        $select = $this->getLayout()->createBlock(
            \Magento\Framework\View\Element\Html\Select::class
        )->setData(
            ['id' => 'quick_search_type_id', 'class' => 'select guest-select']
        )->setName(
            'oar_type'
        )->setOptions(
            $this->_getFormOptions()
        )->setExtraParams(
            'onchange="showIdentifyBlock(this.value);"'
        );
        return $select->getHtml();
    }

    /**
     * Get Form Options for Guest
     *
     * @return array
     */
    protected function _getFormOptions()
    {
        $options = $this->getData('identifymeby_options');
        if ($options === null) {
            $options = [];
            $options[] = ['value' => 'email', 'label' => 'Email Address'];
            $options[] = ['value' => 'zip', 'label' => 'ZIP Code'];
            $this->setData('identifymeby_options', $options);
        }

        return $options;
    }

    /**
     * Return quick search form action url
     *
     * @return string
     */
    public function getActionUrl()
    {
        return $this->getUrl('sales/guest/view');
    }
}
