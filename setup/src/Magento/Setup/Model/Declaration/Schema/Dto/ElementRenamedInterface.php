<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Declaration\Schema\Dto;

/**
 * This interface says, that element was renamed and new name should be specified for element
 * Rename attribute do not participate in diff operation
 */
interface ElementRenamedInterface
{
    /**
     * Return old name of structural element
     *
     * @return string
     */
    public function wasRenamedFrom();
}
