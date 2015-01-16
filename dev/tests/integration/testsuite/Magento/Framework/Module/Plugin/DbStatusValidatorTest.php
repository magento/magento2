<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\Module\Plugin;

class DbStatusValidatorTest extends \Magento\TestFramework\TestCase\AbstractController
{
    public function testValidationUpToDateDb()
    {
        $this->dispatch('index/index');
    }

    /**
     * @magentoDbIsolation enabled
     * @expectedException \Magento\Framework\Module\Exception
     * @expectedExceptionMessage Please update your database
     */
    public function testValidationOutdatedDb()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        /** @var \Magento\Framework\Module\ModuleListInterface $moduleList */
        $moduleList = $objectManager->get('Magento\Framework\Module\ModuleListInterface');

        /** @var \Magento\Framework\Module\ResourceResolverInterface $resourceResolver */
        $resourceResolver = $objectManager->get('Magento\Framework\Module\ResourceResolverInterface');

        // get first resource, we don't care which one it is.
        foreach ($moduleList->getNames() as $moduleName) {
            if ($resourceList = $resourceResolver->getResourceList($moduleName)) {
                $resourceName = $resourceList[0];
                break;
            }
        }

        // Prepend '0.' to DB Version, to cause it to be an older version
        /** @var \Magento\Framework\Module\ResourceInterface $resource */
        $resource = $objectManager->create('Magento\Framework\Module\ResourceInterface');
        $currentDbVersion = $resource->getDbVersion($resourceName);
        $resource->setDbVersion($resourceName, '0.' . $currentDbVersion);

        /** @var \Magento\Framework\Cache\FrontendInterface $cache */
        $cache = $this->_objectManager->get('Magento\Framework\App\Cache\Type\Config');
        $cache->clean();

        /* This triggers plugin to be executed */
        $this->dispatch('index/index');
    }
}
