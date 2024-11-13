<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Reflection\Test\Unit\Fixture;

class TSample extends TSampleAbstract implements TSampleInterface
{
    /**
     * @inheritdoc
     */
    public function getPropertyName()
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return '';
    }

    /**
     * @inheritdoc
     */
    public function getWithNull()
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function getOnlyNull()
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function getDataOverridden()
    {
        return [];
    }
}
