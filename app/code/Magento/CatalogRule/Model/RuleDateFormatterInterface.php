<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\CatalogRule\Model;

/**
 * Local date for catalog rule
 */
interface RuleDateFormatterInterface
{
    /**
     * Create \DateTime object with date converted to scope timezone for catalog rule
     *
     * @param mixed $scope Information about scope
     * @return \DateTime
     */
    public function getDate($scope = null): \DateTime;

    /**
     * Get scope timestamp for catalog rule
     *
     * @param mixed $scope Information about scope
     * @return int
     */
    public function getTimeStamp($scope = null): int;
}
