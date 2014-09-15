<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Framework\Search\Adapter\Mysql;

use Magento\Framework\App\ScopeResolverInterface;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\Search\Request\Dimension;

class Dimensions
{
    const DEFAULT_DIMENSION_NAME = 'scope';

    const STORE_FIELD_NAME = 'store_id';

    /**
     * @var \Magento\Framework\App\Resource
     */
    private $resource;
    /**
     * @var ScopeResolverInterface
     */
    private $scopeResolver;

    /**
     * @param \Magento\Framework\App\Resource $resource
     * @param ScopeResolverInterface $scopeResolver
     */
    public function __construct(
        \Magento\Framework\App\Resource $resource,
        ScopeResolverInterface $scopeResolver
    ) {
        $this->resource = $resource;
        $this->scopeResolver = $scopeResolver;
    }

    /**
     * @param Dimension $dimension
     * @return string
     */
    public function build(Dimension $dimension)
    {
        /** @var AdapterInterface $adapter */
        $adapter = $this->resource->getConnection(\Magento\Framework\App\Resource::DEFAULT_READ_RESOURCE);

        return $this->generateExpression($dimension, $adapter);
    }

    /**
     * @param Dimension $dimension
     * @param AdapterInterface $adapter
     * @return string
     */
    private function generateExpression(Dimension $dimension, AdapterInterface $adapter)
    {
        $identifier = $dimension->getName();
        $value = $dimension->getValue();

        if (self::DEFAULT_DIMENSION_NAME === $identifier) {
            $identifier = self::STORE_FIELD_NAME;
            $value = $this->scopeResolver->getScope($value)->getId();
        }

        return sprintf(
            '%s = %s',
            $adapter->quoteIdentifier($identifier),
            $adapter->quote($value)
        );
    }
}
