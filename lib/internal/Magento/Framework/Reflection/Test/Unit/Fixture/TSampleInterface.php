<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Reflection\Test\Unit\Fixture;

interface TSampleInterface
{
    /**
     * Returns property name for a sample.
     *
     * @return string
     */
    public function getPropertyName();

    /**
     * Doc block without return tag.
     */
    public function getName();
}
