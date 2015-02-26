<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Dhl\Model\Resource;

class Setup extends \Magento\Framework\Module\DataSetup
{
    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $localeResolver;

    /**
     * @param \Magento\Framework\Module\Setup\Context $context
     * @param string $resourceName
     * @param string $moduleName
     * @param \Magento\Framework\Locale\ResolverInterface $localeResolver
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Module\Setup\Context $context,
        $resourceName,
        $moduleName,
        \Magento\Framework\Locale\ResolverInterface $localeResolver,
        $connectionName = \Magento\Framework\Module\Updater\SetupInterface::DEFAULT_SETUP_CONNECTION
    ) {
        $this->localeResolver = $localeResolver;
        parent::__construct($context, $resourceName, $moduleName, $connectionName);
    }

    /**
     * @return \Magento\Framework\Locale\ResolverInterface
     */
    public function getLocaleResolver()
    {
        return $this->localeResolver;
    }
}
