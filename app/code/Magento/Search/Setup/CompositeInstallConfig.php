<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Search\Setup;

use Magento\Framework\App\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Composite object uses the proper InstallConfigInterface implementation for the engine being configured
 */
class CompositeInstallConfig implements InstallConfigInterface
{
    /**
     * @var InstallConfigInterface[]
     */
    private $installConfigList;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param InstallConfigInterface[] $installConfigList
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        array $installConfigList = []
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->installConfigList = $installConfigList;
    }

    /**
     * @inheritDoc
     */
    public function configure(array $inputOptions)
    {
        if (isset($inputOptions['search-engine'])) {
            $searchEngine = $inputOptions['search-engine'];
        } else {
            $searchEngine = $this->scopeConfig->getValue('catalog/search/engine');
        }

        if (isset($this->installConfigList[$searchEngine]) && !empty($inputOptions)) {
            $installConfig = $this->installConfigList[$searchEngine];
            $installConfig->configure($inputOptions);

            //Clean config so new configuration is loaded
            if ($this->scopeConfig instanceof Config) {
                $this->scopeConfig->clean();
            }
        }
    }
}
