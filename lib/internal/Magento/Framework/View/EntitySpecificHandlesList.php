<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View;

/**
 * Model which allows access to handles containing ID of the rendered entity
 * @since 2.2.0
 */
class EntitySpecificHandlesList
{
    /**
     * The list of handles containing entity ID
     *
     * @var string[]
     * @since 2.2.0
     */
    private $handles = [];

    /**
     * Add handle to the list of handles containing entity ID
     *
     * @param string $handle
     * @return void
     * @since 2.2.0
     */
    public function addHandle($handle)
    {
        $this->handles[] = $handle;
    }

    /**
     * Get list of handles containing entity ID
     *
     * @return string[]
     * @since 2.2.0
     */
    public function getHandles()
    {
        return $this->handles;
    }
}
