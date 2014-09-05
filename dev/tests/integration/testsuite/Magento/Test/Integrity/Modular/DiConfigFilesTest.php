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
namespace Magento\Test\Integrity\Modular;

class DiConfigFilesTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Primary DI configs from app/etc
     * @var array
     */
    protected static $_primaryFiles = array();

    /**
     * Global DI configs from all modules
     * @var array
     */
    protected static $_moduleGlobalFiles = array();

    /**
     * Area DI configs from all modules
     * @var array
     */
    protected static $_moduleAreaFiles = array();

    protected function _prepareFiles()
    {
        //init primary configs
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var $filesystem \Magento\Framework\App\Filesystem */
        $filesystem = $objectManager->get('Magento\Framework\App\Filesystem');
        $configDirectory = $filesystem->getDirectoryRead(\Magento\Framework\App\Filesystem::CONFIG_DIR);
        $fileIteratorFactory = $objectManager->get('Magento\Framework\Config\FileIteratorFactory');
        self::$_primaryFiles = $fileIteratorFactory->create(
            $configDirectory,
            $configDirectory->search('{*/di.xml,di.xml}')
        );
        //init module global configs
        /** @var $modulesReader \Magento\Framework\Module\Dir\Reader */
        $modulesReader = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get('Magento\Framework\Module\Dir\Reader');
        self::$_moduleGlobalFiles = $modulesReader->getConfigurationFiles('di.xml');

        //init module area configs
        $areas = array('adminhtml', 'frontend');
        foreach ($areas as $area) {
            $moduleAreaFiles = $modulesReader->getConfigurationFiles($area . '/di.xml');
            self::$_moduleAreaFiles[$area] = $moduleAreaFiles;
        }
    }

    /**
     * @param string $xml
     * @return void
     * @dataProvider linearFilesProvider
     */
    public function testDiConfigFileWithoutMerging($xml)
    {
        /** @var \Magento\Framework\ObjectManager\Config\SchemaLocator $schemaLocator */
        $schemaLocator = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\ObjectManager\Config\SchemaLocator'
        );

        $dom = new \DOMDocument();
        $dom->loadXML($xml);
        if (!@$dom->schemaValidate($schemaLocator->getSchema())) {
            $this->fail('File ' . $xml . ' has invalid xml structure.');
        }
    }

    public function linearFilesProvider()
    {
        if (empty(self::$_primaryFiles)) {
            $this->_prepareFiles();
        }

        $common = array_merge(self::$_primaryFiles->toArray(), self::$_moduleGlobalFiles->toArray());

        foreach (self::$_moduleAreaFiles as $files) {
            $common = array_merge($common, $files->toArray());
        }

        $output = [];
        foreach ($common as $path => $file) {
            $output[$path] = [$file];
        }

        return $output;
    }

    /**
     * @param array $files
     * @dataProvider mixedFilesProvider
     */
    public function testMergedDiConfig(array $files)
    {
        $mapperMock = $this->getMock('Magento\Framework\ObjectManager\Config\Mapper\Dom', array(), array(), '', false);
        $fileResolverMock = $this->getMock('Magento\Framework\Config\FileResolverInterface');
        $fileResolverMock->expects($this->any())->method('read')->will($this->returnValue($files));
        $validationStateMock = $this->getMock('Magento\Framework\Config\ValidationStateInterface');
        $validationStateMock->expects($this->any())->method('isValidated')->will($this->returnValue(true));

        /** @var \Magento\Framework\ObjectManager\Config\SchemaLocator $schemaLocator */
        $schemaLocator = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\ObjectManager\Config\SchemaLocator'
        );

        new \Magento\Framework\ObjectManager\Config\Reader\Dom(
            $fileResolverMock,
            $mapperMock,
            $schemaLocator,
            $validationStateMock
        );
    }

    public function mixedFilesProvider()
    {
        if (empty(self::$_primaryFiles)) {
            $this->_prepareFiles();
        }
        foreach (self::$_primaryFiles->toArray() as $file) {
            $primaryFiles[] = array(array($file));
        }
        $primaryFiles['all primary config files'] = array(self::$_primaryFiles->toArray());

        foreach (self::$_moduleGlobalFiles->toArray() as $file) {
            $moduleFiles[] = array(array($file));
        }
        $moduleFiles['all module global config files'] = array(self::$_moduleGlobalFiles->toArray());

        $areaFiles = array();
        foreach (self::$_moduleAreaFiles as $area => $files) {
            foreach ($files->toArray() as $file) {
                $areaFiles[] = array(array($file));
            }
            $areaFiles["all {$area} config files"] = array(self::$_moduleAreaFiles[$area]->toArray());
        }

        return $primaryFiles + $moduleFiles + $areaFiles;
    }
}
