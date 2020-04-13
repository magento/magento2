<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Search\Setup;

use Magento\Config\Model\Config\Backend\Admin\Custom;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\InputException;

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
    public function __construct(ScopeConfigInterface $scopeConfig, array $installConfigList)
    {
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
            $searchEngine = $this->scopeConfig->getValue(Custom::XML_PATH_CATALOG_SEARCH_ENGINE);
        }

        if (!isset($this->installConfigList[$searchEngine])) {
            throw new InputException(__('Unable to configure search engine: %1', $searchEngine));
        }
        $installConfig = $this->installConfigList[$searchEngine];

        $installConfig->configure($inputOptions);
    }
}
