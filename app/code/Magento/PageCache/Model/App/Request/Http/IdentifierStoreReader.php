<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PageCache\Model\App\Request\Http;

use Magento\Store\Model\StoreManager;

class IdentifierStoreReader
{
    /**
     * @var \Magento\Framework\View\DesignExceptions
     */
    private $designExceptions;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var \Magento\PageCache\Model\Config
     */
    private $config;

    /**
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
     * @param array $data
     * @return array|null
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getPageTagsWithStoreCacheTags(array $data): ?array
    {
        if ($this->config->getType() === \Magento\PageCache\Model\Config::BUILT_IN && $this->config->isEnabled()) {
            $ruleDesignException = $this->designExceptions->getThemeByRequest($this->request);
            if ($ruleDesignException !== false) {
                $data['DESIGN'] = $ruleDesignException;
            }

            if ($runType = $this->request->getServerValue(StoreManager::PARAM_RUN_TYPE)) {
                $data[StoreManager::PARAM_RUN_TYPE] = $runType;
            }

            if ($runCode = $this->request->getServerValue(StoreManager::PARAM_RUN_CODE)) {
                $data[StoreManager::PARAM_RUN_CODE] = $runCode;
            }
        }

        return $data;
    }
}
