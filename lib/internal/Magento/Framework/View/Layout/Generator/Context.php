<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\View\Layout\Generator;

use Magento\Framework\View\Layout;
use Magento\Framework\View\LayoutInterface;

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
