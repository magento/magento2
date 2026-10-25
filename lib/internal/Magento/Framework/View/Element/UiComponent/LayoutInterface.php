<?php
/**
 * Copyright 2015 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\View\Element\UiComponent;

use Magento\Framework\View\Element\UiComponentInterface;

/**
 * Interface LayoutInterface
 *
 * @api
 */
interface LayoutInterface
{
    const SECTIONS_KEY = 'sections';

    const AREAS_KEY = 'areas';

    const GROUPS_KEY = 'groups';

    const ELEMENTS_KEY = 'elements';

    const DATA_SOURCE_KEY = 'data_source';

    /**
     * @param UiComponentInterface $component
     * @return array
     */
    public function build(UiComponentInterface $component);
}
