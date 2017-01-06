<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * CatalogSearch Index Engine Interface
 */
namespace Magento\CatalogSearch\Model\ResourceModel;

interface EngineInterface
{
    /**
     * Configuration path by which current indexer handler stored
     */
    const CONFIG_ENGINE_PATH = 'catalog/search/engine';

    /**
     * Retrieve allowed visibility values for current engine
     *
     * @return array
     */
    public function getAllowedVisibility();

    /**
     * Define if current search engine supports advanced index
     *
     * @return bool
     */
    public function allowAdvancedIndex();

    /**
     * Prepare attribute value to store in index
     *
     * @param \Magento\Eav\Model\Entity\Attribute $attribute
     * @param mixed $value
     * @return mixed
     */
    public function processAttributeValue($attribute, $value);


    /**
     * Prepare index array as a string glued by separator
     *
     * @param array $index
     * @param string $separator
     * @return string
     */
    public function prepareEntityIndex($index, $separator = ' ');
}
