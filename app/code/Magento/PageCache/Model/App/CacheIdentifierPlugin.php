<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\PageCache\Model\App;

use Magento\Store\Model\StoreManager;

/**
 * Class CachePlugin
 *
 * Should add design exceptions o identifier for built-in cache
 */
class CacheIdentifierPlugin
{
    /**
     * Constructor
     *
     * @param \Magento\Framework\View\DesignExceptions $designExceptions
     * @param \Magento\Framework\App\RequestInterface $request
     * @param \Magento\PageCache\Model\Config $config
     */
    public function __construct(
        \Magento\Framework\View\DesignExceptions $designExceptions,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\PageCache\Model\Config $config
    ) {
        $this->designExceptions = $designExceptions;
        $this->request = $request;
        $this->config = $config;
    }

    /**
     * Adds a theme key to identifier for a built-in cache if user-agent theme rule is actual
     *
     * @param \Magento\Framework\App\PageCache\Identifier $identifier
     * @param string $result
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetValue(\Magento\Framework\App\PageCache\Identifier $identifier, $result)
    {
        if ($this->config->getType() == \Magento\PageCache\Model\Config::BUILT_IN && $this->config->isEnabled()) {
            $identifierPrefix = '';

            $ruleDesignException = $this->designExceptions->getThemeByRequest($this->request);
            if ($ruleDesignException !== false) {
                $identifierPrefix .= 'DESIGN' . '=' . $ruleDesignException . '|';
            }

            if ($runType = $this->request->getServerValue(StoreManager::PARAM_RUN_TYPE)) {
                $identifierPrefix .= StoreManager::PARAM_RUN_TYPE . '=' .  $runType . '|';
            }

            if ($runCode = $this->request->getServerValue(StoreManager::PARAM_RUN_CODE)) {
                $identifierPrefix .= StoreManager::PARAM_RUN_CODE . '=' . $runCode . '|';
            }

            return $identifierPrefix . $result;

        }
        return $result;
    }
}
