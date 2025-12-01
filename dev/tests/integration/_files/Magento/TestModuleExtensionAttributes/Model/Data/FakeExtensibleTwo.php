<?php
/**
 *
 * Copyright 2018 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\TestModuleExtensionAttributes\Model\Data;

use Magento\Framework\Api\AbstractExtensibleObject;
use Magento\TestModuleExtensionAttributes\Api\Data\FakeExtensibleTwoInterface;

class FakeExtensibleTwo extends AbstractExtensibleObject implements FakeExtensibleTwoInterface
{
    /**
     * {@inheritdoc}
     */
    public function getExtensionAttributes()
    {
        return $this->_getExtensionAttributes();
    }
}
