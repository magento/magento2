<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Theme\Block\Html;

use Magento\Framework\Escaper;

/**
 * Html page header block
 *
 * @api
 * @since 100.0.2
 */
class Header extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * Constructor
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param Magento\Framework\Escaper $escaper
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Escaper $escaper,
        array $data = []
    ) {
        $this->escaper = $escaper;
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
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        }
        $this->_data['welcome'] =  $this->escaper->escapeQuote($this->_data['welcome'], true);
        return __($this->_data['welcome']);
    }
}
