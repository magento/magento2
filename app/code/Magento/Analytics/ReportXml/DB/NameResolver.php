<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Analytics\ReportXml\DB;

/**
 * Class NameResolver
 *
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
