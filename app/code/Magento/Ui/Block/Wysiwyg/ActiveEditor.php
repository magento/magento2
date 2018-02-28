<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Block\Wysiwyg;

use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Ui\Model;

/**
 * ActiveEditor block
 *
 * @api
 */
class ActiveEditor extends \Magento\Framework\View\Element\Template
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param Context $context
     * @param ScopeConfigInterface $scopeConfig
     * @param array $data
     */
    public function __construct(Context $context, ScopeConfigInterface $scopeConfig, array $data = [])
    {
        parent::__construct($context, $data);
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Get active wysiwyg adapter path
     *
     * @return string
     */
    public function getWysiwygAdapterPath()
    {
        $adapterPath = $this->scopeConfig->getValue(Model\Config::WYSIWYG_EDITOR_CONFIG_PATH);
        return $this->escapeHtml($adapterPath);
    }
}
