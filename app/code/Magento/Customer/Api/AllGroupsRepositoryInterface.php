<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Api;

use Magento\Framework\Exception\LocalizedException;

/**
 * Customer all groups interface
 * @since 100.0.2
 */
interface AllGroupsRepositoryInterface
{
    /**
     * Retrieve all customer groups.
     *
     * This call returns an array of objects, but detailed information about each object’s attributes might not be
     * included. See http://devdocs.magento.com/codelinks/attributes.html#GroupRepositoryInterface to determine
     * which call to use to get detailed information about all attributes for an object.
     *
     * @return \Magento\Customer\Api\Data\GroupSearchResultsInterface
     * @throws LocalizedException
     */
    public function getAllGroups();
}
