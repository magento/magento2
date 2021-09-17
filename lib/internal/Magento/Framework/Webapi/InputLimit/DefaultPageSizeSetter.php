<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Framework\Webapi\InputLimit;

use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Webapi\Validator\ConfigProvider;

/**
 * Sets the default page size with the configured input limits
 */
class DefaultPageSizeSetter
{
    /**
     * @var ConfigProvider
     */
    private $validationConfigProvider;

    /**
     * @param ConfigProvider $validationConfigProvider
     */
    public function __construct(ConfigProvider $validationConfigProvider)
    {
        $this->validationConfigProvider = $validationConfigProvider;
    }

    /**
     * Set the default page size if needed using the optional parameter as a fallback value
     *
     * @param SearchCriteriaInterface $searchCriteria The search criteria to manipulate
     * @param int|null $defaultPageSizeFallback The fallback value if limiting is enabled but a limit has not been set
     */
    public function processSearchCriteria(
        SearchCriteriaInterface $searchCriteria,
        int $defaultPageSizeFallback = null
    ): void {
        if ($searchCriteria->getPageSize() === null
            && $this->validationConfigProvider->isInputLimitingEnabled()
        ) {
            $searchCriteria->setPageSize(
                $this->validationConfigProvider->getDefaultPageSize() ?? $defaultPageSizeFallback
            );
        }
    }
}
