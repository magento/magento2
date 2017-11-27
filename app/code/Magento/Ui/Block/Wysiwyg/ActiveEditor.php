<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Block\Wysiwyg;

use \Magento\Framework\View\Element\Template\Context;
use \Magento\Framework\App\Config\ScopeConfigInterface;
use \Magento\Cms\Model;

/**
 * ActiveEditor block
 *
 * @api
 */
class ActiveEditor extends \Magento\Framework\View\Element\Template
{
    private $scopeConfig;

    public function __construct(Context $context, ScopeConfigInterface $scopeConfig, array $data = [])
    {
        parent::__construct($context, $data);
        $this->scopeConfig = $scopeConfig;
    }

    public function getWysiwygAdapterPath()
    {
        $adapterPath = $this->scopeConfig->getValue(Model\Wysiwyg\Config::WYSIWYG_EDITOR_CONFIG_PATH);
        return $adapterPath;
    }
}
