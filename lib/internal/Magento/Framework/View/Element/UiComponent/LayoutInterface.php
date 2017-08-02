<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\UiComponent;

use Magento\Framework\View\Element\UiComponentInterface;

/**
 * Interface LayoutInterface
 * @since 2.0.0
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
     * @since 2.0.0
     */
    public function build(UiComponentInterface $component);
}
