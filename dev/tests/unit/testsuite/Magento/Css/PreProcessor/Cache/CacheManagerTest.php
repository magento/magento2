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

namespace Magento\Css\PreProcessor\Cache;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

class CacheManagerTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Css\PreProcessor\Cache\CacheManager */
    protected $cacheManager;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Css\PreProcessor\Cache\Import\Map\Storage|\PHPUnit_Framework_MockObject_MockObject */
    protected $storageMock;

    /** @var \Magento\Css\PreProcessor\Cache\Import\ImportEntityFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $importEntityFactoryMock;

    protected function setUp()
    {
        $this->storageMock = $this->getMock('Magento\Css\PreProcessor\Cache\Import\Map\Storage', [], [], '', false);
        $this->importEntityFactoryMock = $this->getMock(
            'Magento\Css\PreProcessor\Cache\Import\ImportEntityFactory',
            [],
            [],
            '',
            false
        );

        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->cacheManager = $this->objectManagerHelper->getObject(
            'Magento\Css\PreProcessor\Cache\CacheManager',
            [
                'storage' => $this->storageMock,
                'importEntityFactory' => $this->importEntityFactoryMock,
                'filePath' => 'Magento_Core::style.css',
                'params' => ['theme' => 'some_theme', 'area' => 'frontend', 'locale' => 'en_US']
            ]
        );
    }

    public function testClearCache()
    {
        $expectedKey = 'Magento_Core::style.css|frontend|en_US|some_theme';

        $fileKeyProperty = new \ReflectionProperty($this->cacheManager, 'uniqueFileKey');
        $fileKeyProperty->setAccessible(true);
        $this->assertEquals($expectedKey, $fileKeyProperty->getValue($this->cacheManager));

        $cachedFileProperty = new \ReflectionProperty($this->cacheManager, 'cachedFile');
        $cachedFileProperty->setAccessible(true);
        $cachedFileProperty->setValue($this->cacheManager, 'some_cachedFile');

        $importEntitiesProperty = new \ReflectionProperty($this->cacheManager, 'importEntities');
        $importEntitiesProperty->setAccessible(true);
        $this->assertEquals([], $importEntitiesProperty->getValue($this->cacheManager));
        $importEntitiesProperty->setValue($this->cacheManager, ['some_import_1', 'some_import_2']);

        $this->storageMock->expects($this->once())
            ->method('delete')
            ->with($this->equalTo($expectedKey))
            ->will($this->returnSelf());

        $this->assertEquals($this->cacheManager, $this->cacheManager->clearCache());
        $this->assertNull($cachedFileProperty->getValue($this->cacheManager));
        $this->assertEquals([], $importEntitiesProperty->getValue($this->cacheManager));
    }

    public function testGetCachedFile()
    {
        $property = new \ReflectionProperty($this->cacheManager, 'cachedFile');
        $property->setAccessible(true);
        $this->assertNull($property->getValue($this->cacheManager));
        $property->setValue($this->cacheManager, 'test');
        $this->assertEquals('test', $this->cacheManager->getCachedFile());
    }

    /**
     * @param array $params
     * @param array $expectedResult
     * @dataProvider addEntityToCacheDataProvider
     */
    public function testAddEntityToCache($params, $expectedResult)
    {
        $importEntitiesProperty = new \ReflectionProperty($this->cacheManager, 'importEntities');
        $importEntitiesProperty->setAccessible(true);
        $this->assertEquals([], $importEntitiesProperty->getValue($this->cacheManager));

        $this->importEntityFactoryMock->expects($this->any())->method('create')
            ->with($this->anything(), $this->anything())->will($this->returnValue('entity_object_here'));
        foreach ($params as $value) {
            $this->assertEquals(
                $this->cacheManager,
                $this->cacheManager->addEntityToCache($value['filePath'], $value['viewParams'])
            );
        }
        $this->assertEquals($expectedResult, $importEntitiesProperty->getValue($this->cacheManager));
    }

    /**
     * @return array
     */
    public function addEntityToCacheDataProvider()
    {
        $themeModelMockId = $this->getMock('Magento\Core\Model\Theme', [], [], '', false);
        $themeModelMockId->expects($this->once())->method('getId')->will($this->returnValue('1'));

        $themeModelMockPath = $this->getMock('Magento\Core\Model\Theme', [], [], '', false);
        $themeModelMockPath->expects($this->once())->method('getThemePath')->will($this->returnValue('mocked_path'));
        return [
            'one import' => [
                'params' => [
                    [
                        'filePath' => 'css\some_file.css',
                        'viewParams' => ['theme' => 'other_theme', 'area' => 'backend', 'locale' => 'fr_FR']
                    ]
                ],
                'expectedResult' => ['css\some_file.css|backend|fr_FR|other_theme' => 'entity_object_here']
            ],
            'one import with theme id' => [
                'params' => [
                    [
                        'filePath' => 'css\theme_id\some_file.css',
                        'viewParams' => ['themeModel' => $themeModelMockId, 'area' => 'backend', 'locale' => 'en_En']
                    ]
                ],
                'expectedResult' => ['css\theme_id\some_file.css|backend|en_En|1' => 'entity_object_here']
            ],
            'one import with theme path' => [
                'params' => [
                    [
                        'filePath' => 'css\some_file.css',
                        'viewParams' => ['themeModel' => $themeModelMockPath, 'area' => 'frontend']
                    ]
                ],
                'expectedResult' => ['css\some_file.css|frontend|088d309371332feb12bad4dbf93cfb5d'
                    => 'entity_object_here']
            ],
            'list of imports' => [
                'params' => [
                    [
                        'filePath' => 'Magento_Core::folder\file.css',
                        'viewParams' => ['theme' => 'theme_path', 'area' => 'backend']
                    ],
                    [
                        'filePath' => 'calendar\button.css',
                        'viewParams' => ['theme' => 'theme_path', 'area' => 'backend', 'locale' => 'en_US']
                    ],
                ],
                'expectedResult' => [
                    'Magento_Core::folder\file.css|backend|theme_path' => 'entity_object_here',
                    'calendar\button.css|backend|en_US|theme_path' => 'entity_object_here',
                ]
            ],
        ];
    }

    /**
     * @param string $uniqueFileKey
     * @param array $expected
     * @dataProvider saveCacheDataProvider
     */
    public function testSaveCache($uniqueFileKey, $expected)
    {
        $importEntitiesProperty = new \ReflectionProperty($this->cacheManager, 'importEntities');
        $importEntitiesProperty->setAccessible(true);
        $this->assertEquals([], $importEntitiesProperty->getValue($this->cacheManager));
        $importEntitiesProperty->setValue($this->cacheManager, $expected['imports']);

        $this->storageMock->expects($this->once())
            ->method('save')
            ->with($this->equalTo($uniqueFileKey), $this->equalTo(serialize($expected)))
            ->will($this->returnSelf());
        $this->assertEquals($this->cacheManager, $this->cacheManager->saveCache($expected['cached_file']));
    }

    /**
     * @return array
     */
    public function saveCacheDataProvider()
    {
        return [
            [
                'Magento_Core::style.css|frontend|en_US|some_theme',
                [
                    'cached_file' => 'file-to-save.css',
                    'imports' => ['import1', 'import2', 'import3']
                ]
            ]
        ];
    }

    /**
     * @param Import\ImportEntity[] $importData
     * @param bool $expected
     * @dataProvider isValidDataProvider
     */
    public function testIsValid($importData, $expected)
    {
        $importEntitiesProperty = new \ReflectionProperty($this->cacheManager, 'importEntities');
        $importEntitiesProperty->setAccessible(true);
        $this->assertEquals([], $importEntitiesProperty->getValue($this->cacheManager));
        $importEntitiesProperty->setValue($this->cacheManager, $importData);

        $method = new \ReflectionMethod('Magento\Css\PreProcessor\Cache\CacheManager', 'isValid');
        $method->setAccessible(true);

        $this->assertEquals($expected, $method->invoke($this->cacheManager));
    }

    /**
     * @return array
     */
    public function isValidDataProvider()
    {
        $importEntityTrue = $this->getMock('Magento\Css\PreProcessor\Cache\Import\ImportEntity', [], [], '', false);
        $importEntityTrue->expects($this->once())->method('isValid')->will($this->returnValue(true));

        $importEntityFalse = $this->getMock('Magento\Css\PreProcessor\Cache\Import\ImportEntity', [], [], '', false);
        $importEntityFalse->expects($this->once())->method('isValid')->will($this->returnValue(false));
        return [
            [[$importEntityTrue], true],
            [[$importEntityFalse], false],
            [[], false]
        ];
    }
}
