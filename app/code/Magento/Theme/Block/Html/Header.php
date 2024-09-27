<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Theme\Block\Html;

use Magento\Framework\App\ObjectManager;
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
     * @var Escaper
     */
    private $escaper;

    /**
     * @param Context $context
     * @param array $data
     * @param Escaper|null $escaper
     */
    public function __construct(
        Context $context,
        array $data = [],
        Escaper $escaper = null
    ) {
        parent::__construct($context, $data);
        $this->escaper = $escaper ?: ObjectManager::getInstance()->get(Escaper::class);
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
