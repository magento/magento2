<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Theme\Block\Html;

/**
 * Html page header block
 *
 * @api
 * @since 2.0.0
 */
class Header extends \Magento\Framework\View\Element\Template
{
    /**
     * Current template name
     *
     * @var string
     * @since 2.0.0
     */
    protected $_template = 'html/header.phtml';

    /**
     * Retrieve welcome text
     *
     * @return string
     * @since 2.0.0
     */
    public function getWelcome()
    {
        if (empty($this->_data['welcome'])) {
            $this->_data['welcome'] = $this->_scopeConfig->getValue(
                'design/header/welcome',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        }
        return __($this->_data['welcome']);
    }
}
