<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Integrity\Modular;

use Magento\Framework\Component\ComponentRegistrar;

class ResourcesConfigFilesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Framework\App\ResourceConnection\Config\Reader
     */
    protected $_model;

    protected function setUp(): void
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var $moduleDirSearch \Magento\Framework\Component\DirSearch */
        $moduleDirSearch = $objectManager->get(\Magento\Framework\Component\DirSearch::class);
        $fileIteratorFactory = $objectManager->get(\Magento\Framework\Config\FileIteratorFactory::class);
        $xmlFiles = $fileIteratorFactory->create(
            $moduleDirSearch->collectFiles(ComponentRegistrar::MODULE, 'etc/{*/resources.xml,resources.xml}')
        );

        $fileResolverMock = $this->createMock(\Magento\Framework\Config\FileResolverInterface::class);
        $fileResolverMock->expects($this->any())->method('get')->willReturn($xmlFiles);
        $validationStateMock = $this->createMock(\Magento\Framework\Config\ValidationStateInterface::class);
        $validationStateMock->expects($this->any())->method('isValidationRequired')->willReturn(true);
        $deploymentConfigMock = $this->createPartialMock(
            \Magento\Framework\App\DeploymentConfig::class,
            ['getConfiguration']
        );
        $deploymentConfigMock->expects($this->any())->method('getConfiguration')->willReturn([]);
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->_model = $objectManager->create(
            \Magento\Framework\App\ResourceConnection\Config\Reader::class,
            [
                'fileResolver' => $fileResolverMock,
                'validationState' => $validationStateMock,
                'deploymentConfig' => $deploymentConfigMock
            ]
        );
    }

    public function testResourcesXmlFiles()
    {
        $this->_model->read('global');
    }
}
