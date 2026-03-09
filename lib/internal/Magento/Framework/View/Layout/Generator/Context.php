<?php
/**
 * Copyright 2014 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\View\Layout\Generator;

use Magento\Framework\View\Layout;
use Magento\Framework\View\LayoutInterface;

/**
 * @api
 * @since 100.0.2
 */
class Context
{
    /**
     * @var Layout\Data\Structure
     */
    protected $structure;

    /**
     * @var LayoutInterface
     */
    protected $layout;

    /**
     * Constructor
     *
     * @param Layout\Data\Structure $structure
     * @param LayoutInterface $layout
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
     */
    public function getStructure()
    {
        return $this->structure;
    }

    /**
     * @return LayoutInterface
     */
    public function getLayout()
    {
        return $this->layout;
    }
}
