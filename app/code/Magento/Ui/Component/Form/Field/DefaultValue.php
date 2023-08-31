<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Ui\Component\Form\Field;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\Component\Form\Field;

/**
 * Field class has dynamic default value based on  System Configuration path
 */
class DefaultValue extends Field
{
    /**
     * DefaultValue constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param string $path
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        private readonly ScopeConfigInterface $scopeConfig,
        private readonly StoreManagerInterface $storeManager,
        private string $path = '',
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * @inheritdoc
     */
    public function prepare()
    {
        parent::prepare();
        $store = $this->storeManager->getStore();
        if (empty($this->_data['config']['default'])) {
            $this->_data['config']['default'] = $this->scopeConfig->getValue(
                $this->path,
                ScopeInterface::SCOPE_STORE,
                $store
            );
        }
    }
}
