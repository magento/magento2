<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare (strict_types=1);

namespace Magento\Framework\View\Asset\PreProcessor;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State;
use Magento\Framework\View\Asset\Minification;

/**
 * Minification configuration provider
 */
class MinificationConfigProvider
{
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
     * Check whether asset minification is on
     *
     * @param string $filename
     * @return bool
     */
    public function isMinificationEnabled(string $filename): bool
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
