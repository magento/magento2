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
namespace Magento\Css\PreProcessor\Cache\Import;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

class CacheTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Css\PreProcessor\Cache\Import\Cache */
    protected $cache;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Css\PreProcessor\Cache\Import\Map\Storage|\PHPUnit_Framework_MockObject_MockObject */
    protected $storageMock;

    /** @var \Magento\Css\PreProcessor\Cache\Import\ImportEntityFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $importEntityFactoryMock;

    /** @var \Magento\Filesystem\Directory\ReadInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $rootDirectory;

    protected function setUp()
    {
        $this->storageMock = $this->getMock(
            'Magento\Css\PreProcessor\Cache\Import\Map\Storage',
            array(),
            array(),
            '',
            false
        );
        $this->rootDirectory = $this->getMock(
            'Magento\Filesystem\Directory\ReadInterface',
            array(),
            array(),
            '',
            false
        );
        $this->importEntityFactoryMock = $this->getMock(
            'Magento\Css\PreProcessor\Cache\Import\ImportEntityFactory',
            array(),
            array(),
            '',
            false
        );

        $cssFile = $this->getMock('Magento\View\Publisher\CssFile', array(), array(), '', false);
        $cssFile->expects($this->once())->method('getFilePath')->will($this->returnValue('Magento_Core::style.css'));
        $cssFile->expects(
            $this->once()
        )->method(
            'getViewParams'
        )->will(
            $this->returnValue(array('theme' => 'some_theme', 'area' => 'frontend', 'locale' => 'en_US'))
        );

        $fileFactory = $this->getMock('Magento\View\Publisher\FileFactory', array(), array(), '', false);
        $fileFactory->expects($this->any())->method('create')->will($this->returnValue($cssFile));

        $filesystem = $this->getMock('Magento\App\Filesystem', array(), array(), '', false);
        $filesystem->expects($this->any())->method('getDirectoryRead')->will($this->returnValue($this->rootDirectory));

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->cache = $this->objectManagerHelper->getObject(
            'Magento\Css\PreProcessor\Cache\Import\Cache',
            array(
                'storage' => $this->storageMock,
                'importEntityFactory' => $this->importEntityFactoryMock,
                'filesystem' => $filesystem,
                'fileFactory' => $fileFactory,
                'publisherFile' => $cssFile
            )
        );
    }

    public function testClearCache()
    {
        $expectedKey = 'Magento_Core::style.css|frontend|en_US|some_theme';

        $fileKeyProperty = new \ReflectionProperty($this->cache, 'uniqueFileKey');
        $fileKeyProperty->setAccessible(true);
        $this->assertEquals($expectedKey, $fileKeyProperty->getValue($this->cache));

        $cachedFileProperty = new \ReflectionProperty($this->cache, 'cachedFile');
        $cachedFileProperty->setAccessible(true);
        $cachedFileProperty->setValue($this->cache, 'some_cachedFile');

        $importEntitiesProperty = new \ReflectionProperty($this->cache, 'importEntities');
        $importEntitiesProperty->setAccessible(true);
        $this->assertEquals(array(), $importEntitiesProperty->getValue($this->cache));
        $importEntitiesProperty->setValue($this->cache, array('some_import_1', 'some_import_2'));

        $this->storageMock->expects(
            $this->once()
        )->method(
            'delete'
        )->with(
            $this->equalTo($expectedKey)
        )->will(
            $this->returnSelf()
        );

        $this->assertEquals($this->cache, $this->cache->clear());
        $this->assertEmpty($cachedFileProperty->getValue($this->cache));
        $this->assertEquals(array(), $importEntitiesProperty->getValue($this->cache));
    }

    public function testGetCachedFile()
    {
        $property = new \ReflectionProperty($this->cache, 'cachedFile');
        $property->setAccessible(true);
        $this->assertEmpty($property->getValue($this->cache));
        $property->setValue(
            $this->cache,
            $this->getMock('Magento\View\Publisher\CssFile', array(), array(), '', false)
        );
        $this->assertInstanceOf('\Magento\View\Publisher\CssFile', $this->cache->get());
    }

    /**
     * @param array $params
     * @param array $expectedResult
     * @dataProvider addEntityToCacheDataProvider
     */
    public function testAddEntityToCache($params, $expectedResult)
    {
        $importEntitiesProperty = new \ReflectionProperty($this->cache, 'importEntities');
        $importEntitiesProperty->setAccessible(true);
        $this->assertEquals(array(), $importEntitiesProperty->getValue($this->cache));

        $this->importEntityFactoryMock->expects(
            $this->any()
        )->method(
            'create'
        )->with(
            $this->isInstanceOf('Magento\Less\PreProcessor\File\Less')
        )->will(
            $this->returnValue('entity_object_here')
        );

        foreach ($params as $value) {
            $this->assertEquals($this->cache, $this->cache->add($value));
        }
        $this->assertEquals($expectedResult, $importEntitiesProperty->getValue($this->cache));
    }

    /**
     * @return array
     */
    public function addEntityToCacheDataProvider()
    {
        $themeModelMockId = $this->getMock('Magento\Core\Model\Theme', array(), array(), '', false);
        $themeModelMockId->expects($this->once())->method('getId')->will($this->returnValue('1'));

        $themeModelMockPath = $this->getMock('Magento\Core\Model\Theme', array(), array(), '', false);
        $themeModelMockPath->expects($this->once())->method('getThemePath')->will($this->returnValue('mocked_path'));
        return array(
            'one import' => array(
                'params' => $this->getLessFile(
                    array(
                        array(
                            'filePath' => 'css\some_file.css',
                            'viewParams' => array('theme' => 'other_theme', 'area' => 'backend', 'locale' => 'fr_FR')
                        )
                    )
                ),
                'expectedResult' => array('file_id_1' => 'entity_object_here')
            ),
            'one import with theme id' => array(
                'params' => $this->getLessFile(
                    array(
                        array(
                            'filePath' => 'css\theme_id\some_file.css',
                            'viewParams' => array('themeModel' => $themeModelMockId, 'locale' => 'en_En')
                        )
                    )
                ),
                'expectedResult' => array('file_id_1' => 'entity_object_here')
            ),
            'one import with theme path' => array(
                'params' => $this->getLessFile(
                    array(
                        array(
                            'filePath' => 'css\some_file.css',
                            'viewParams' => array('themeModel' => $themeModelMockPath, 'area' => 'frontend')
                        )
                    )
                ),
                'expectedResult' => array('file_id_1' => 'entity_object_here')
            ),
            'list of imports' => array(
                'params' => $this->getLessFile(
                    array(
                        array(
                            'filePath' => 'Magento_Core::folder\file.css',
                            'viewParams' => array('theme' => 'theme_path', 'area' => 'backend')
                        ),
                        array(
                            'filePath' => 'calendar\button.css',
                            'viewParams' => array('theme' => 'theme_path', 'area' => 'backend', 'locale' => 'en_US')
                        )
                    )
                ),
                'expectedResult' => array('file_id_1' => 'entity_object_here', 'file_id_2' => 'entity_object_here')
            )
        );
    }

    /**
     * @param array $filesData
     * @return \Magento\Less\PreProcessor\File\Less|\PHPUnit_Framework_MockObject_MockObject
     */
    protected function getLessFile($filesData)
    {
        $lessFiles = array();
        $fileCounter = 0;
        $fileCounterCallback = $this->returnCallback(
            function () use (&$fileCounter) {
                return 'file_id_' . ++$fileCounter;
            }
        );
        foreach ($filesData as $fileData) {
            $readDirectory = $this->getMock('Magento\Filesystem\Directory\ReadInterface', array(), array(), '', false);
            $readDirectory->expects(
                $this->any()
            )->method(
                'stat'
            )->with(
                'file_path'
            )->will(
                $this->returnValue(isset($fileData['mtime']) ? $fileData['mtime'] : null)
            );
            $lessFile = $this->getMock('Magento\Less\PreProcessor\File\Less', array(), array(), '', false);
            $lessFile->expects($this->any())->method('getFilePath')->will($this->returnValue($fileData['filePath']));
            $lessFile->expects(
                $this->any()
            )->method(
                'getViewParams'
            )->will(
                $this->returnValue($fileData['viewParams'])
            );
            $lessFile->expects($this->any())->method('getFileIdentifier')->will($fileCounterCallback);
            $lessFile->expects($this->any())->method('getDirectoryRead')->will($this->returnValue($readDirectory));
            $lessFiles[] = $lessFile;
        }
        return $lessFiles;
    }

    /**
     * @param \Magento\View\Publisher\CssFile $cssFile
     * @param string $uniqueFileKey
     * @param array $expected
     * @dataProvider saveCacheDataProvider
     */
    public function testSaveCache($cssFile, $uniqueFileKey, $expected)
    {
        $importEntitiesProperty = new \ReflectionProperty($this->cache, 'importEntities');
        $importEntitiesProperty->setAccessible(true);
        $this->assertEquals(array(), $importEntitiesProperty->getValue($this->cache));
        $importEntitiesProperty->setValue($this->cache, $expected['imports']);

        $this->storageMock->expects(
            $this->once()
        )->method(
            'save'
        )->with(
            $this->equalTo($uniqueFileKey),
            $this->equalTo(serialize($expected))
        )->will(
            $this->returnSelf()
        );
        $this->assertEquals($this->cache, $this->cache->save($cssFile));
    }

    /**
     * @return array
     */
    public function saveCacheDataProvider()
    {
        $objectManager = new \Magento\TestFramework\Helper\ObjectManager($this);
        $arguments = $objectManager->getConstructArguments(
            'Magento\View\Publisher\CssFile',
            array('viewParams' => array('area' => 'frontend'))
        );

        $cssFile = $objectManager->getObject('Magento\View\Publisher\CssFile', $arguments);

        return array(
            array(
                $cssFile,
                'Magento_Core::style.css|frontend|en_US|some_theme',
                array('cached_file' => $cssFile, 'imports' => array('import1', 'import2', 'import3'))
            )
        );
    }

    /**
     * @param array $filesData
     * @param int $baseTime
     * @param bool $expected
     * @dataProvider isValidDataProvider
     */
    public function testIsValid($filesData, $baseTime, $expected)
    {
        $factoryCallback = $this->returnCallback(
            function ($lessFile) {
                /** @var $lessFile \Magento\Less\PreProcessor\File\Less|\PHPUnit_Framework_MockObject_MockObject */
                $importEntity = $this->getMock(
                    'Magento\Css\PreProcessor\Cache\Import\ImportEntity',
                    array(),
                    array(),
                    '',
                    false
                );
                $changeTime = $lessFile->getDirectoryRead()->stat('file_path');
                $importEntity->expects(
                    $this->atLeastOnce()
                )->method(
                    'getOriginalMtime'
                )->will(
                    $this->returnValue($changeTime)
                );
                return $importEntity;
            }
        );
        $this->importEntityFactoryMock->expects($this->any())->method('create')->will($factoryCallback);
        $files = $this->getLessFile($filesData);
        foreach ($files as $file) {
            $this->cache->add($file);
        }
        $this->rootDirectory->expects(
            $this->any()
        )->method(
            'stat'
        )->will(
            $this->returnValue(array('mtime' => $baseTime))
        );
        $this->assertEquals($expected, $this->cache->isValid());
    }

    /**
     * @return array
     */
    public function isValidDataProvider()
    {
        return array(
            array(
                'filesData' => array(
                    array(
                        'filePath' => 'Magento_Core::folder\file.css',
                        'viewParams' => array('theme' => 'theme_path', 'area' => 'backend'),
                        'mtime' => 12345
                    ),
                    array(
                        'filePath' => 'calendar\button.css',
                        'viewParams' => array('theme' => 'theme_path', 'area' => 'backend', 'locale' => 'en_US'),
                        'mtime' => 12345
                    )
                ),
                'baseTime' => 12345,
                'expected' => true
            ),
            array(
                'filesData' => array(
                    array(
                        'filePath' => 'Magento_Core::folder\file.css',
                        'viewParams' => array('theme' => 'theme_path', 'area' => 'backend'),
                        'mtime' => 12345
                    )
                ),
                'baseTime' => 54321,
                'expected' => false
            ),
            array('filesData' => array(), 'baseTime' => 12345, 'expected' => false)
        );
    }
}
