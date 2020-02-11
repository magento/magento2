<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Mod\HelloWorldApi\Model;

use Mod\HelloWorldApi\Api\Data\ExtraAbilitiesInterface;

/**
 * Customer ExtraAbility class.
 */
class ExtraAbility implements ExtraAbilitiesInterface
{
    /** @var  array */
    private $isAllowedAddDescription;

    /** @var  int */
    private $abilityId;

    /** @var  int */
    private $customerId;

    /**
     * @inheritdoc
     */
    public function getIsAllowedAddDescription()
    {
        return $this->isAllowedAddDescription;
    }

    /**
     * @inheritdoc
     */
    public function setIsAllowedAddDescription(int $isAllowedAddDescription)
    {
        $this->isAllowedAddDescription = $isAllowedAddDescription;
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getAbilityId()
    {
        return $this->abilityId;
    }

    /**
     * @inheritdoc
     */
    public function setAbilityId(int $abilityId)
    {
        $this->abilityId = $abilityId;
        return $this->abilityId;
    }

    /**
     * @inheritdoc
     */
    public function getCustomerId()
    {
        return $this->customerId;
    }

    /**
     * @inheritdoc
     */
    public function setCustomerId(int $id)
    {
        $this->customerId = $id;
        return $this;
    }
}
