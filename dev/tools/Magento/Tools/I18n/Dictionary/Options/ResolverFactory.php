<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Tools\I18n\Dictionary\Options;

/**
 * Options resolver factory
 */
class ResolverFactory
{
    /**
     * Default option resolver class
     */
    const DEFAULT_RESOLVER = 'Magento\Tools\I18n\Dictionary\Options\Resolver';

    /**
     * @var string
     */
    protected $resolverClass;

    /**
     * @param string $resolverClass
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
     */
    public function create($directory, $withContext)
    {
        $resolver = new $this->resolverClass($directory, $withContext);
        if (!$resolver instanceof ResolverInterface) {
            throw new \InvalidArgumentException($this->resolverClass . ' doesn\'t implement ResolverInterface');
        }
        return $resolver;
    }
}
