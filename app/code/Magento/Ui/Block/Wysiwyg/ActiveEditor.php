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
    const DEFAULT_EDITOR_PATH = 'mage/adminhtml/wysiwyg/tiny_mce/tinymce4Adapter';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var array
     */
    private $availableAdapterPaths;

    /**
     * ActiveEditor constructor.
     * @param Context $context
     * @param ScopeConfigInterface $scopeConfig
     * @param array $availableAdapterPaths
     * @param array $data
     */
    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig,
        $availableAdapterPaths = [],
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->scopeConfig = $scopeConfig;
        $this->availableAdapterPaths = $availableAdapterPaths;
    }

    /**
     * Get active wysiwyg adapter path
     *
     * @return string
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
