<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Asset\PreProcessor;

use Magento\Framework\View\Asset\Minification;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State;

/**
 * Class MinificationFilenameResolver
 */
class MinificationFilenameResolver implements FilenameResolverInterface
{
    /**
     * Indicator of minification file
     */
    const FILE_PART = '.min.';

    /**
     * @var Minification
     */
    private $minification;

    /**
     * @var State
     */
    private $appState;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Constructor
     *
     * @param Minification $minification
     * @param ScopeConfigInterface $scopeConfig
     * @param State $appState
     */
    public function __construct(
        Minification $minification,
        ScopeConfigInterface $scopeConfig,
        State $appState
    ) {
        $this->minification = $minification;
        $this->scopeConfig = $scopeConfig;
        $this->appState = $appState;
    }

    /**
     * Resolve file name
     *
     * @param string $path
     * @return string
     */
    public function resolve($path)
    {
        $result = $path;
        if ($this->isEnabledForArea($path)) {
            $result = str_replace(self::FILE_PART, '.', $path);
        }

        return $result;
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
