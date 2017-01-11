<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View;

/**
 * Model which allows access to handles containing ID of the rendered entity
 */
class EntitySpecificHandlesList
{
    /**
     * The list of handles containing entity ID
     *
     * @var string[]
     */
    private $handles = [];

    /**
     * Add handle to the list of handles containing entity ID
     *
     * @param string $handle
     * @return void
     */
    public function addHandle($handle)
    {
        $this->handles[] = $handle;
    }

    /**
     * Get list of handles containing entity ID
     *
     * @return string[]
     */
    public function getHandles()
    {
        return $this->handles;
    }
}
