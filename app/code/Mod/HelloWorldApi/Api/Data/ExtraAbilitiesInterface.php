<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Mod\HelloWorldApi\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Customer extra abilities interface.
 * @api
 */
interface ExtraAbilitiesInterface extends ExtensibleDataInterface
{
    const IS_ALLOWED_ADD_DESCRIPTION = "is_allowed_add_description";

    const CUSTOMER_ID = "customer_id";

    const ABILITY_ID = "ability_id";

    /**
     * Retrieve allow to add extra comment.
     *
     * @return int
     */
    public function getIsAllowedAddDescription();

    /**
     * Set allow to add extra comment.
     *
     * @param int $isAllowedAddDescription
     * @return self
     */
    public function setIsAllowedAddDescription(int $isAllowedAddDescription);

    /**
     * Retrieve customer id.
     *
     * @return int
     */
    public function getCustomerId();

    /**
     * Set Customer Id for further updates.
     *
     * @param int $id
     * @return self
     */
    public function setCustomerId(int $id);

    /**
     * Retrieve ability id.
     *
     * @return int
     */
    public function getAbilityId();

    /**
     * Set ability id.
     *
     * @param int $abilityId
     * @return self
     */
    public function setAbilityId(int $abilityId);
}
