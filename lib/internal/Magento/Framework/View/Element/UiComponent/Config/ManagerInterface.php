<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\View\Element\UiComponent\Config;

/**
 * Interface ManagerInterface
 * @deprecated 2.2.0 since 2.2.0
 * @since 2.0.0
 */
interface ManagerInterface
{
    /**
     * Search pattern
     */
    const SEARCH_PATTERN = '%s.xml';

    /**
     * The anonymous template name
     */
    const ANONYMOUS_TEMPLATE = 'anonymous_%s_component_%d';

    /**
     * The key arguments in the data component
     */
    const COMPONENT_ARGUMENTS_KEY = 'arguments';

    /**
     * The key attributes in the data component
     */
    const COMPONENT_ATTRIBUTES_KEY = 'attributes';

    /**
     * The array key sub components
     */
    const CHILDREN_KEY = 'children';

    /**
     * Prepare the initialization data of UI components
     *
     * @param string $name
     * @return ManagerInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     * @since 2.0.0
     */
    public function prepareData($name);

    /**
     * Get component data
     *
     * @param string $name
     * @return array
     * @since 2.0.0
     */
    public function getData($name);

    /**
     * To create the raw  data components
     *
     * @param string $component
     * @return array
     * @since 2.0.0
     */
    public function createRawComponentData($component);

    /**
     * Get UIReader and collect base files configuration
     *
     * @param string $name
     * @return UiReaderInterface
     * @since 2.0.0
     */
    public function getReader($name);
}
