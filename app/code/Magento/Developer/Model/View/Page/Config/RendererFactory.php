<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Developer\Model\View\Page\Config;

use Magento\Developer\Model\Config\Source\WorkflowType;
use Magento\Store\Model\ScopeInterface;

/**
 * Factory class for \Magento\Framework\View\Page\Config\RendererInterface
 */
class RendererFactory extends \Magento\Framework\View\Page\Config\RendererFactory
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Renderer Types
     *
     * @var array
     */
    private $rendererTypes;

    /**
     * Factory constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param array $rendererTypes
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        array $rendererTypes = []
    ) {
        $this->objectManager = $objectManager;
        $this->scopeConfig = $scopeConfig;
        $this->rendererTypes = $rendererTypes;
    }

    /**
     * Create class instance
     *
     * @param array $data
     *
     * @return \Magento\Framework\View\Page\Config\RendererInterface
     */
    public function create(array $data = [])
    {
        $renderer = $this->scopeConfig->getValue(WorkflowType::CONFIG_NAME_PATH, ScopeInterface::SCOPE_STORE);

        return $this->objectManager->create(
            $this->rendererTypes[$renderer],
            $data
        );
    }
}
