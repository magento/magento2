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

    /**
     * return annotation with a null type at first position
     * @return null|string
     */
    public function getWithNull();

    /**
     * return annotation with only null type
     * @return null
     */
    public function getOnlyNull();
}
