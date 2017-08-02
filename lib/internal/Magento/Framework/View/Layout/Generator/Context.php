<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Layout\Generator;

use Magento\Framework\View\Layout;
use Magento\Framework\View\LayoutInterface;

/**
 * @api
 * @since 2.0.0
 */
class Context
{
    /**
     * @var Layout\Data\Structure
     * @since 2.0.0
     */
    protected $structure;

    /**
     * @var LayoutInterface
     * @since 2.0.0
     */
    protected $layout;

    /**
     * Constructor
     *
     * @param Layout\Data\Structure $structure
     * @param LayoutInterface $layout
     * @since 2.0.0
     */
    public function __construct(
        Layout\Data\Structure $structure,
        LayoutInterface $layout
    ) {
        $this->structure = $structure;
        $this->layout = $layout;
    }

    /**
     * @return \Magento\Framework\View\Layout\Data\Structure
     * @since 2.0.0
     */
    public function getStructure()
    {
        return $this->structure;
    }

    /**
     * @return LayoutInterface
     * @since 2.0.0
     */
    public function getLayout()
    {
        return $this->layout;
    }
}
