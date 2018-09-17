<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Developer\Model\View\Asset\PreProcessor;

use Magento\Framework\View\Asset\PreProcessor;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Developer\Model\Config\Source\WorkflowType;
use Magento\Framework\View\Asset\PreProcessorInterface;
use Magento\Framework\View\Asset\PreProcessor\AlternativeSourceInterface;

/**
 * Class PreprocessorStrategy
 */
class PreprocessorStrategy implements PreProcessorInterface
{
    /**
     * @var FrontendCompilation
     */
    private $frontendCompilation;

    /**
     * @var AlternativeSourceInterface
     */
    private $alternativeSource;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Constructor
     *
     * @param AlternativeSourceInterface $alternativeSource
     * @param FrontendCompilation $frontendCompilation
     * @param ScopeConfigInterface $scopeConfig
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
     * Transform content and/or content type for the specified preprocessing chain object
     *
     * @param PreProcessor\Chain $chain
     * @return void
     */
    public function process(PreProcessor\Chain $chain)
    {
        if (WorkflowType::CLIENT_SIDE_COMPILATION === $this->scopeConfig->getValue(WorkflowType::CONFIG_NAME_PATH)) {
            $this->frontendCompilation->process($chain);
        } else {
            $this->alternativeSource->process($chain);
        }
    }
}
