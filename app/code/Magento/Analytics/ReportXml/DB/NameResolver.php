<?php
/**
 * Copyright 2017 Adobe
 * All Rights Reserved.
 */
namespace Magento\Analytics\ReportXml\DB;

/**
 * Resolver for source names
 */
class NameResolver
{
    /**
     * Returns element for name
     *
     * @param array $elementConfig
     * @return string
     */
    public function getName($elementConfig)
    {
        return $elementConfig['name'];
    }

    /**
     * Returns alias
     *
     * @param array $elementConfig
     * @return string
     */
    public function getAlias($elementConfig)
    {
        $alias = $this->getName($elementConfig);
        if (isset($elementConfig['alias'])) {
            $alias = $elementConfig['alias'];
        }
        return $alias;
    }
}
