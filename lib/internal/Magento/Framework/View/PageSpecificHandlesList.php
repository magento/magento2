<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View;

use Magento\Framework\View\LayoutInterface;

/**
 * Layout model which allows access to handles specific to current page only (e.g. containing ID of the rendered entity)
 */
class PageSpecificHandlesList
{
    /**
     * @var LayoutInterface
     */
    private $layout;

    /**
     * The list of handles containing entity ID
     *
     * @var string[]
     */
    private $handles = [];

    /**
     * Initialize dependencies
     *
     * @param \Magento\Framework\View\LayoutInterface $layout
     */
    public function __construct(LayoutInterface $layout)
    {
        $this->layout = $layout;
    }

    /**
     * Add handle to the list of handles containing entity ID
     *
     * @param string $handle
     * @return void
     */
    public function addHandle($handle)
    {
        $this->layout->getUpdate()->addHandle($handle);
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
