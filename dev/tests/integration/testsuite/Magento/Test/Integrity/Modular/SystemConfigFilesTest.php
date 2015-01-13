<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Integrity\Modular;

use Magento\Framework\App\Filesystem\DirectoryList;

class SystemConfigFilesTest extends \PHPUnit_Framework_TestCase
{
    public function testConfiguration()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();

        // disable config caching to not pollute it
        /** @var $cacheState \Magento\Framework\App\Cache\StateInterface */
        $cacheState = $objectManager->get('Magento\Framework\App\Cache\StateInterface');
        $cacheState->setEnabled(\Magento\Framework\App\Cache\Type\Config::TYPE_IDENTIFIER, false);

        /** @var \Magento\Framework\Filesystem $filesystem */
        $filesystem = $objectManager->get('Magento\Framework\Filesystem');
        $modulesDir = $filesystem->getDirectoryRead(DirectoryList::MODULES)->getAbsolutePath();

        $fileList = glob($modulesDir . '/*/*/etc/adminhtml/system.xml');

        $configMock = $this->getMock(
            'Magento\Framework\Module\Dir\Reader',
            ['getConfigurationFiles', 'getModuleDir'],
            [],
            '',
            false
        );
        $configMock->expects($this->any())->method('getConfigurationFiles')->will($this->returnValue($fileList));
        $configMock->expects(
            $this->any()
        )->method(
            'getModuleDir'
        )->with(
            'etc',
            'Magento_Backend'
        )->will(
            $this->returnValue($modulesDir . '/Magento/Backend/etc')
        );
        try {
            $objectManager->create(
                'Magento\Backend\Model\Config\Structure\Reader',
                ['moduleReader' => $configMock, 'runtimeValidation' => true]
            );
        } catch (\Magento\Framework\Exception $exp) {
            $this->fail($exp->getMessage());
        }
    }
}
