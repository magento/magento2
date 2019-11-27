<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Asset\PreProcessor;

use Magento\Framework\Code\Minifier\AdapterInterface;
use Magento\Framework\View\Asset\Minification;
use Magento\Framework\View\Asset\PreProcessor;
use Magento\Framework\View\Asset\PreProcessorInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State;

/**
 * Assets minification pre-processor
 */
class Minify implements PreProcessorInterface
{
    /**
     * @var AdapterInterface
     */
    protected $adapter;

    /**
     * @var Minification
     */
    protected $minification;

    /**
     * @var State
     */
    private $appState;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param AdapterInterface $adapter
     * @param Minification $minification
     */
    public function __construct(
        AdapterInterface $adapter,
        Minification $minification,
        ScopeConfigInterface $scopeConfig,
        State $appState
    ) {
        $this->adapter = $adapter;
        $this->minification = $minification;
        $this->scopeConfig = $scopeConfig;
        $this->appState = $appState;
    }

    /**
     * Transform content and/or content type for the specified preprocessing chain object
     *
     * @param PreProcessor\Chain $chain
     * @return void
     */
    public function process(PreProcessor\Chain $chain)
    {
        if ($this->isEnabledForArea($chain->getTargetAssetPath()) &&
            $this->minification->isMinifiedFilename($chain->getTargetAssetPath()) &&
            !$this->minification->isMinifiedFilename($chain->getOrigAssetPath())
        ) {
            $content = $this->adapter->minify($chain->getContent());
            $chain->setContent($content);
        }
    }

    /**
     * Check whether asset minification is on for specified content type and for area
     *
     * @param string $filename
     * @return bool
     */
    private function isEnabledForArea(string $filename): bool
    {
        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $result = $this->minification->isEnabled($extension);
        $pathParts = explode('/', $filename);
        if (!empty($pathParts) && isset($pathParts[0])) {
            $area = $pathParts[0];
            if ($area === 'adminhtml') {
                $result = $this->appState->getMode() != State::MODE_DEVELOPER &&
                    $this->scopeConfig->isSetFlag(
                        sprintf(Minification::XML_PATH_MINIFICATION_ENABLED, $extension),
                        'default'
                    );
            }
        }
        return $result;
    }
}
