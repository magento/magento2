<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Developer\Model\View\Asset\PreProcessor;

use Magento\Developer\Model\Config\Source\WorkflowType;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\State;
use Magento\Framework\View\Asset\PreProcessor;
use Magento\Framework\View\Asset\PreProcessor\AlternativeSourceInterface;
use Magento\Framework\View\Asset\PreProcessorInterface;

/**
 * Selection of the strategy for assets pre-processing
 *
 * @api
 * @since 2.0.0
 */
class PreprocessorStrategy implements PreProcessorInterface
{
    /**
     * @var FrontendCompilation
     * @since 2.0.0
     */
    private $frontendCompilation;

    /**
     * @var AlternativeSourceInterface
     * @since 2.0.0
     */
    private $alternativeSource;

    /**
     * @var ScopeConfigInterface
     * @since 2.0.0
     */
    private $scopeConfig;

    /**
     * @var State
     * @since 2.2.0
     */
    private $state;

    /**
     * Constructor
     *
     * @param AlternativeSourceInterface $alternativeSource
     * @param FrontendCompilation $frontendCompilation
     * @param ScopeConfigInterface $scopeConfig
     * @since 2.0.0
     */
    public function __construct(
        AlternativeSourceInterface $alternativeSource,
        FrontendCompilation $frontendCompilation,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->frontendCompilation = $frontendCompilation;
        $this->alternativeSource = $alternativeSource;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Transform content and/or content type for the specified pre-processing chain object
     *
     * @param PreProcessor\Chain $chain
     * @return void
     * @since 2.0.0
     */
    public function process(PreProcessor\Chain $chain)
    {
        $isClientSideCompilation =
            $this->getState()->getMode() !== State::MODE_PRODUCTION
            && WorkflowType::CLIENT_SIDE_COMPILATION === $this->scopeConfig->getValue(WorkflowType::CONFIG_NAME_PATH);

        if ($isClientSideCompilation) {
            $this->frontendCompilation->process($chain);
        } else {
            $this->alternativeSource->process($chain);
        }
    }

    /**
     * @return State
     * @deprecated 2.2.0
     * @since 2.2.0
     */
    private function getState()
    {
        if (null === $this->state) {
            $this->state = ObjectManager::getInstance()->get(State::class);
        }
        return $this->state;
    }
}
