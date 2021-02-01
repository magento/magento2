<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
     *
     */
    public function testValidationOutdatedDb()
    {
        $this->expectExceptionMessage("Please upgrade your database");
        $this->expectException(\Magento\Framework\Exception\LocalizedException::class);
        $this->markTestSkipped();
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        /** @var \Magento\Framework\Module\ModuleListInterface $moduleList */
        $moduleList = $objectManager->get(\Magento\Framework\Module\ModuleListInterface::class);

        $moduleNameToTest = '';

        // get first module name, we don't care which one it is.
        foreach ($moduleList->getNames() as $moduleName) {
            $moduleNameToTest = $moduleName;
            break;
        }
        $moduleList->getOne($moduleName);

        // Prepend '0.' to DB Version, to cause it to be an older version
        /** @var \Magento\Framework\Module\ResourceInterface $resource */
        $resource = $objectManager->create(\Magento\Framework\Module\ResourceInterface::class);
        $currentDbVersion = $resource->getDbVersion($moduleNameToTest);
        $resource->setDataVersion($moduleNameToTest, '0.' . $currentDbVersion);

        /** @var \Magento\Framework\Cache\FrontendInterface $cache */
        $cache = $this->_objectManager->get(\Magento\Framework\App\Cache\Type\Config::class);
        $cache->clean();

        /* This triggers plugin to be executed */
        $this->dispatch('index/index');
    }
}
