<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Framework\Search;

/**
 * @api
 * @since 102.0.0
 */
interface EngineResolverInterface
{
    /**
     * Returns Current Search Engine
     *
     * It returns string identifier of Search Engine that is currently chosen in configuration
     *
     * @return string
     * @since 102.0.0
     */
    public function getCurrentSearchEngine();
}
