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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
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
        /** @var $dir \Magento\App\Dir */
        $dir = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\App\Dir');

        $configPath = $dir->getDir(\Magento\App\Dir::APP) . DS . 'etc' . DS . '*' . DS;
        self::$_primaryFiles = glob($configPath . DS. 'di.xml');
        array_unshift(self::$_primaryFiles, $dir->getDir(\Magento\App\Dir::APP) . DS . 'etc' . DS . 'di.xml');

        //init module global configs
        /** @var $modulesReader \Magento\Core\Model\Config\Modules\Reader */
        $modulesReader = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get('Magento\Core\Model\Config\Modules\Reader');
        self::$_moduleGlobalFiles = $modulesReader->getConfigurationFiles('di.xml');

        //init module area configs
        $areas = array('adminhtml', 'frontend');
        foreach ($areas as $area) {
            $moduleAreaFiles = $modulesReader->getConfigurationFiles($area . DS . 'di.xml');
            self::$_moduleAreaFiles[$area] = $moduleAreaFiles;
        }
    }

    /**
     * @param string $file
     * @return void
     * @dataProvider linearFilesProvider
     */
    public function testDiConfigFileWithoutMerging($file)
    {
        /** @var \Magento\ObjectManager\Config\SchemaLocator $schemaLocator */
        $schemaLocator = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get('Magento\ObjectManager\Config\SchemaLocator');

        $dom = new \DOMDocument();
        $dom->load($file);
        if (!@$dom->schemaValidate($schemaLocator->getSchema())) {
            $this->fail('File ' . $file . ' has invalid xml structure.');
        }
    }

    public function linearFilesProvider()
    {
        if (empty(self::$_primaryFiles)) {
            $this->_prepareFiles();
        }

        $common = array_merge(self::$_primaryFiles, self::$_moduleGlobalFiles);

        foreach (self::$_moduleAreaFiles as $files) {
            $common = array_merge($common, $files);
        }

        $output = array();
        foreach ($common as $file) {
            $output[$file] = array($file);
        }

        return $output;
    }

    /**
     * @param array $files
     * @dataProvider mixedFilesProvider
     */
    public function testMergedDiConfig(array $files)
    {
        $mapperMock = $this->getMock('Magento\ObjectManager\Config\Mapper\Dom', array(), array(), '', false);
        $fileResolverMock = $this->getMock('Magento\Config\FileResolverInterface');
        $fileResolverMock->expects($this->any())->method('read')->will($this->returnValue($files));
        $validationStateMock = $this->getMock('Magento\Config\ValidationStateInterface');
        $validationStateMock->expects($this->any())->method('isValidated')->will($this->returnValue(true));

        /** @var \Magento\ObjectManager\Config\SchemaLocator $schemaLocator */
        $schemaLocator = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get('Magento\ObjectManager\Config\SchemaLocator');

        new \Magento\ObjectManager\Config\Reader\Dom(
            $fileResolverMock, $mapperMock, $schemaLocator, $validationStateMock
        );
    }

    public function mixedFilesProvider()
    {
        if (empty(self::$_primaryFiles)) {
            $this->_prepareFiles();
        }
        foreach (self::$_primaryFiles as $file) {
            $primaryFiles[$file] = array(array($file));
        }
        $primaryFiles['all primary config files'] = array(self::$_primaryFiles);

        foreach (self::$_moduleGlobalFiles as $file) {
            $moduleFiles[$file] = array(array($file));
        }
        $moduleFiles['all module global config files'] = array(self::$_moduleGlobalFiles);

        $areaFiles = array();
        foreach (self::$_moduleAreaFiles as $area => $files) {
            foreach ($files as $file) {
                $areaFiles[$file] = array(array($file));
            }
            $areaFiles["all $area config files"] = array(self::$_moduleAreaFiles[$area]);
        }

        return $primaryFiles + $moduleFiles + $areaFiles;
    }
}
