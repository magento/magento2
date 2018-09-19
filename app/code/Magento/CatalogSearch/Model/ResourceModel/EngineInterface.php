<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * CatalogSearch Index Engine Interface
 *
 * @deprecated CatalogSearch will be removed in 2.4, and {@see \Magento\ElasticSearch}
 *             will replace it as the default search engine.
 */
namespace Magento\CatalogSearch\Model\ResourceModel;

/**
 * @api
 * @since 100.0.2
 */
interface EngineInterface
{
    const FIELD_PREFIX = 'attr_';

    /**
     * Scope identifier
     *
     * @deprecated since using engine resolver
     * @see \Magento\Framework\Search\EngineResolverInterface
     */
    const SCOPE_IDENTIFIER = 'scope';

    /**
     * Configuration path by which current indexer handler stored
     *
     * @deprecated since using engine resolver
     * @see \Magento\Framework\Search\EngineResolverInterface
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
     * @return array
     */
    public function prepareEntityIndex($index, $separator = ' ');
}
