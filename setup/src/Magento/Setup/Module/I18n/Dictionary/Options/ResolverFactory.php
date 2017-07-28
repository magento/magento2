<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Setup\Module\I18n\Dictionary\Options;

use Magento\Framework\Component\ComponentRegistrar;

/**
 * Options resolver factory
 * @since 2.0.0
 */
class ResolverFactory
{
    /**
     * Default option resolver class
     */
    const DEFAULT_RESOLVER = \Magento\Setup\Module\I18n\Dictionary\Options\Resolver::class;

    /**
     * @var string
     * @since 2.0.0
     */
    protected $resolverClass;

    /**
     * @param string $resolverClass
     * @since 2.0.0
     */
    public function __construct($resolverClass = self::DEFAULT_RESOLVER)
    {
        $this->resolverClass = $resolverClass;
    }

    /**
     * @param string $directory
     * @param bool $withContext
     * @return ResolverInterface
     * @throws \InvalidArgumentException
     * @since 2.0.0
     */
    public function create($directory, $withContext)
    {
        $resolver = new $this->resolverClass(new ComponentRegistrar(), $directory, $withContext);
        if (!$resolver instanceof ResolverInterface) {
            throw new \InvalidArgumentException($this->resolverClass . ' doesn\'t implement ResolverInterface');
        }
        return $resolver;
    }
}
