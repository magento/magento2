<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Search\Setup;

use Magento\Framework\Exception\InputException;
use Magento\Framework\Search\EngineResolverInterface;

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
     * @var EngineResolverInterface
     */
    private $engineResolver;

    /**
     * @param EngineResolverInterface $engineResolver
     * @param InstallConfigInterface[] $installConfigList
     */
    public function __construct(
        EngineResolverInterface $engineResolver,
        array $installConfigList
    ) {
        $this->engineResolver = $engineResolver;
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
            $searchEngine = $this->engineResolver->getCurrentSearchEngine();
        }

        if (!isset($this->installConfigList[$searchEngine])) {
            throw new InputException(__('Unable to configure search engine: %1', $searchEngine));
        }
        $installConfig = $this->installConfigList[$searchEngine];

        $installConfig->configure($inputOptions);
    }
}
