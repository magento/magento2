<?php
/**
 * Validation of DB up to date state
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Framework\Module\Plugin;

use Magento\Framework\Cache\FrontendInterface;

class DbStatusValidator
{
    /**
     * @var FrontendInterface
     */
    private $cache;

    /**
     * @var \Magento\Framework\Module\ModuleListInterface
     */
    private $moduleList;

    /**
     * @var \Magento\Framework\Module\ResourceResolverInterface
     */
    private $resourceResolver;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    private $moduleManager;

    /**
     * @param FrontendInterface $cache
     * @param \Magento\Framework\Module\ModuleListInterface $moduleList
     * @param \Magento\Framework\Module\ResourceResolverInterface $resourceResolver
     * @param \Magento\Framework\Module\Manager $moduleManager
     */
    public function __construct(
        FrontendInterface $cache,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Framework\Module\ResourceResolverInterface $resourceResolver,
        \Magento\Framework\Module\Manager $moduleManager
    ) {
        $this->cache = $cache;
        $this->moduleList = $moduleList;
        $this->resourceResolver = $resourceResolver;
        $this->moduleManager = $moduleManager;
    }

    /**
     * @param \Magento\Framework\App\FrontController $subject
     * @param \Closure $proceed
     * @param \Magento\Framework\App\RequestInterface $request
     *
     * @throws \Magento\Framework\Module\Exception
     * @return \Magento\Framework\App\ResponseInterface
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundDispatch(
        \Magento\Framework\App\FrontController $subject,
        \Closure $proceed,
        \Magento\Framework\App\RequestInterface $request
    ) {
        if (!$this->cache->load('db_is_up_to_date')) {
            if (!$this->isDbUpToDate()) {
                throw new \Magento\Framework\Module\Exception(
                    'Looks like database is outdated. Please, use setup tool to perform update'
                );
            } else {
                $this->cache->save('true', 'db_is_up_to_date');
            }
        }
        return $proceed($request);
    }

    /**
     * Check if DB is up to date
     *
     * @return bool
     */
    private function isDbUpToDate()
    {
        foreach ($this->moduleList->getNames() as $moduleName) {
            foreach ($this->resourceResolver->getResourceList($moduleName) as $resourceName) {
                $isSchemaUpToDate = $this->moduleManager->isDbSchemaUpToDate($moduleName, $resourceName);
                $isDataUpToDate = $this->moduleManager->isDbDataUpToDate($moduleName, $resourceName);
                if (!$isSchemaUpToDate || !$isDataUpToDate) {
                    return false;
                }
            }
        }
        return true;
    }
}
