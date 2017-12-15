<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Model\Declaration\Schema\Dto;

/**
 * This interface can said, that element was renamed and new name should be specified for element
 *
 * If DTO implements this interface - then element can be renamed
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
