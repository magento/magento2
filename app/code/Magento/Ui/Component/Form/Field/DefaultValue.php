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

/**
 * Field class has dynamic default value based on  System Configuration path
 */
class DefaultValue extends \Magento\Ui\Component\Form\Field
{
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var string
     */
    private $path;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    private $storeManager;

    /**
     * DefaultValue constructor.
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param string $path
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        string $path = '',
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->path = $path;
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
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $store
            );
        }
    }
}
