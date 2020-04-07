<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Search\Setup;

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
     * @param InstallConfigInterface[] $installConfigList
     */
    public function __construct(array $installConfigList)
    {
        $this->installConfigList = $installConfigList;
    }

    /**
     * @inheritDoc
     */
    public function configure(array $inputOptions)
    {
        $searchEngine = $inputOptions['search-engine'];

        if (!isset($this->installConfigList[$searchEngine])) {
            throw new InputException(__('Unable to configure search engine: %1', $searchEngine));
        }
        $installConfig = $this->installConfigList[$searchEngine];

        $installConfig->configure($inputOptions);
    }
}
