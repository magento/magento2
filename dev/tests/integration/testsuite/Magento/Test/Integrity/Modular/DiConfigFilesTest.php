<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Test\Integrity\Modular;

use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class DiConfigFilesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Primary DI configs from app/etc
     * @var array
     */
    protected static $_primaryFiles = [];

    /**
     * Global DI configs from all modules
     * @var array
     */
    protected static $_moduleGlobalFiles = [];

    /**
     * Area DI configs from all modules
     * @var array
     */
    protected static $_moduleAreaFiles = [];

    protected static function _prepareFiles()
    {
        //init primary configs
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var $filesystem \Magento\Framework\Filesystem */
        $filesystem = $objectManager->get(\Magento\Framework\Filesystem::class);
        $configDirectory = $filesystem->getDirectoryRead(DirectoryList::CONFIG);
        $fileIteratorFactory = $objectManager->get(\Magento\Framework\Config\FileIteratorFactory::class);
        $search = [];
        foreach ($configDirectory->search('{*/di.xml,di.xml}') as $path) {
            $search[] = $configDirectory->getAbsolutePath($path);
        }
        self::$_primaryFiles = $fileIteratorFactory->create($search);
        //init module global configs
        /** @var $modulesReader \Magento\Framework\Module\Dir\Reader */
        $modulesReader = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get(\Magento\Framework\Module\Dir\Reader::class);
        self::$_moduleGlobalFiles = $modulesReader->getConfigurationFiles('di.xml');

        //init module area configs
        $areas = ['adminhtml', 'frontend'];
        foreach ($areas as $area) {
            $moduleAreaFiles = $modulesReader->getConfigurationFiles($area . '/di.xml');
            self::$_moduleAreaFiles[$area] = $moduleAreaFiles;
        }
    }

    /**
     * @param string $filePath
     * @param string $xml
     * @throws \Exception
     * @dataProvider linearFilesProvider
     */
    public function testDiConfigFileWithoutMerging($filePath, $xml)
    {
        /** @var \Magento\Framework\ObjectManager\Config\SchemaLocator $schemaLocator */
        $schemaLocator = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\ObjectManager\Config\SchemaLocator::class
        );

        $dom = new \DOMDocument();
        $dom->loadXML($xml);

        libxml_use_internal_errors(true);
        $result = \Magento\Framework\Config\Dom::validateDomDocument($dom, $schemaLocator->getSchema());
        libxml_use_internal_errors(false);

        if (!empty($result)) {
            $this->fail(
                'File ' . $filePath . ' has invalid xml structure. '
                . implode("\n", $result)
            );
        }
    }

    public static function linearFilesProvider()
    {
        if (empty(self::$_primaryFiles)) {
            self::_prepareFiles();
        }

        $common = array_merge(self::$_primaryFiles->toArray(), self::$_moduleGlobalFiles->toArray());

        foreach (self::$_moduleAreaFiles as $files) {
            $common = array_merge($common, $files->toArray());
        }

        $output = [];
        foreach ($common as $path => $content) {
            $output[] = [substr($path, strlen(BP)), $content];
        }

        return $output;
    }

    /**
     * @param array $files
     * @dataProvider mixedFilesProvider
     */
    public function testMergedDiConfig(array $files)
    {
        $mapperMock = $this->createMock(\Magento\Framework\ObjectManager\Config\Mapper\Dom::class);
        $fileResolverMock = $this->getMockBuilder(\Magento\Framework\Config\FileResolverInterface::class)
            ->addMethods(['read'])
            ->getMockForAbstractClass();
        $fileResolverMock->expects($this->any())->method('read')->willReturn($files);
        $validationStateMock = $this->createPartialMock(
            \Magento\Framework\Config\ValidationStateInterface::class,
            ['isValidationRequired']
        );
        $validationStateMock->expects($this->any())->method('isValidationRequired')->willReturn(true);

        /** @var \Magento\Framework\ObjectManager\Config\SchemaLocator $schemaLocator */
        $schemaLocator = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            \Magento\Framework\ObjectManager\Config\SchemaLocator::class
        );

        new \Magento\Framework\ObjectManager\Config\Reader\Dom(
            $fileResolverMock,
            $mapperMock,
            $schemaLocator,
            $validationStateMock
        );
    }

    public static function mixedFilesProvider()
    {
        if (empty(self::$_primaryFiles)) {
            self::_prepareFiles();
        }
        foreach (self::$_primaryFiles->toArray() as $file) {
            $primaryFiles[] = [[$file]];
        }
        $primaryFiles['all primary config files'] = [self::$_primaryFiles->toArray()];

        foreach (self::$_moduleGlobalFiles->toArray() as $file) {
            $moduleFiles[] = [[$file]];
        }
        $moduleFiles['all module global config files'] = [self::$_moduleGlobalFiles->toArray()];

        $areaFiles = [];
        foreach (self::$_moduleAreaFiles as $area => $files) {
            foreach ($files->toArray() as $file) {
                $areaFiles[] = [[$file]];
            }
            $areaFiles["all {$area} config files"] = [self::$_moduleAreaFiles[$area]->toArray()];
        }

        return $primaryFiles + $moduleFiles + $areaFiles;
    }
}
