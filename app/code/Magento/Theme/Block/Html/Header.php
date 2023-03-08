<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Theme\Block\Html;

use Magento\Framework\Escaper;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\ScopeInterface;

/**
 * Html page header block
 *
 * @api
 * @since 100.0.2
 */
class Header extends Template
{
    /**
     * Constructor
     *
     * @param Context $context
     * @param Escaper $escaper
     * @param array $data
     */
    public function __construct(
        Context $context,
        private readonly Escaper $escaper,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Current template name
     *
     * @var string
     */
    protected $_template = 'Magento_Theme::html/header.phtml';

    /**
     * Retrieve welcome text
     *
     * @return string
     */
    public function getWelcome()
    {
        if (empty($this->_data['welcome'])) {
            $this->_data['welcome'] = $this->_scopeConfig->getValue(
                'design/header/welcome',
                ScopeInterface::SCOPE_STORE
            );
        }
        return $this->escaper->escapeQuote(__($this->_data['welcome'])->render(), true);
    }
}
