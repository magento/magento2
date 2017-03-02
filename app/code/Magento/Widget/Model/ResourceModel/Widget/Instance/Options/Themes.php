<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Widget\Model\ResourceModel\Widget\Instance\Options;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Theme\Model\ResourceModel\Theme\CollectionFactory as ThemeCollectionFactory;

/**
 * Option source of the widget theme property.
 *
 * Can be used as a data provider for UI components that shows possible themes as a list.
 */
class Themes implements OptionSourceInterface
{
    /**
     * @var ThemeCollectionFactory
     */
    private $themeCollectionFactory;

    /**
     * @param ThemeCollectionFactory $themeCollectionFactory
     */
    public function __construct(ThemeCollectionFactory $themeCollectionFactory)
    {
        $this->themeCollectionFactory = $themeCollectionFactory;
    }

    /**
     * Return array of options as value-label pairs
     *
     * @return array Format: array('<theme ID>' => '<theme label>', ...)
     */
    public function toOptionArray()
    {
        // Load only visible themes that are used in frontend area
        return $this->themeCollectionFactory->create()->loadRegisteredThemes()->toOptionHash();
    }
}
