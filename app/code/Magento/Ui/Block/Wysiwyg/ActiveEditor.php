<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Block\Wysiwyg;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Ui\Model;

/**
 * ActiveEditor block
 *
 * @api
 * @since 101.1.0
 */
class ActiveEditor extends Template
{
    const DEFAULT_EDITOR_PATH = 'mage/adminhtml/wysiwyg/tiny_mce/tinymce5Adapter';

    /**
     * ActiveEditor constructor.
     * @param Context $context
     * @param ScopeConfigInterface $scopeConfig
     * @param array $availableAdapterPaths
     * @param array $data
     */
    public function __construct(
        Context $context,
        private readonly ScopeConfigInterface $scopeConfig,
        private $availableAdapterPaths = [],
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Get active wysiwyg adapter path
     *
     * @return string
     * @since 101.1.0
     */
    public function getWysiwygAdapterPath()
    {
        $adapterPath = $this->scopeConfig->getValue(Model\Config::WYSIWYG_EDITOR_CONFIG_PATH);
        if ($adapterPath !== self::DEFAULT_EDITOR_PATH && !isset($this->availableAdapterPaths[$adapterPath])) {
            $adapterPath = self::DEFAULT_EDITOR_PATH;
        }
        return $this->escapeHtml($adapterPath);
    }
}
