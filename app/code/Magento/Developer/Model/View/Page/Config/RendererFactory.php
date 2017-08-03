<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Developer\Model\View\Page\Config;

use Magento\Developer\Model\Config\Source\WorkflowType;
use Magento\Framework\App\State;
use Magento\Store\Model\ScopeInterface;

/**
 * Factory class for \Magento\Framework\View\Page\Config\RendererInterface
 *
 * @api
 * @since 2.0.0
 */
class RendererFactory extends \Magento\Framework\View\Page\Config\RendererFactory
{
    /**
     * Object Manager instance
     *
     * @var \Magento\Framework\ObjectManagerInterface
     * @since 2.0.0
     */
    protected $objectManager;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     * @since 2.0.0
     */
    protected $scopeConfig;

    /**
     * Renderer Types
     *
     * @var array
     * @since 2.0.0
     */
    private $rendererTypes;

    /**
     * Factory constructor
     *
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param array $rendererTypes
     * @since 2.0.0
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
     * @since 2.0.0
     */
    public function create(array $data = [])
    {
        $renderer = $this->objectManager->get(State::class)->getMode() === State::MODE_PRODUCTION ?
            WorkflowType::SERVER_SIDE_COMPILATION :
            $this->scopeConfig->getValue(WorkflowType::CONFIG_NAME_PATH, ScopeInterface::SCOPE_STORE);

        return $this->objectManager->create(
            $this->rendererTypes[$renderer],
            $data
        );
    }
}
