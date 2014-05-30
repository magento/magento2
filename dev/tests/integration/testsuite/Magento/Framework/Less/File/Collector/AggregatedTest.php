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
namespace Magento\Framework\Less\File\Collector;

class AggregatedTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Less\File\Collector\Aggregated
     */
    protected $model;

    /**
     * @var \Magento\Framework\ObjectManager
     */
    protected $objectManager;

    protected function setUp()
    {
        \Magento\TestFramework\Helper\Bootstrap::getInstance()->reinitialize(
            array(
                \Magento\Framework\App\Filesystem::PARAM_APP_DIRS => array(
                    \Magento\Framework\App\Filesystem::LIB_WEB => array(
                        'path' => dirname(dirname(__DIR__)) . '/_files/lib/web'
                    ),
                    \Magento\Framework\App\Filesystem::THEMES_DIR => array(
                        'path' => dirname(dirname(__DIR__)) . '/_files/design'
                    )
                )
            )
        );
        $this->objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->objectManager->get('Magento\Framework\App\State')->setAreaCode('frontend');

        /** @var \Magento\Framework\Filesystem $filesystem */
        $filesystem = $this->objectManager->create(
            'Magento\Framework\App\Filesystem',
            array(
                'directoryList' => $this->objectManager->create(
                    'Magento\Framework\Filesystem\DirectoryList',
                    array(
                        'root' => BP,
                        'directories' => array(
                            \Magento\Framework\App\Filesystem::MODULES_DIR => array(
                                'path' => dirname(dirname(__DIR__)) . '/_files/code'
                            ),
                            \Magento\Framework\App\Filesystem::THEMES_DIR => array(
                                'path' => dirname(dirname(__DIR__)) . '/_files/design'
                            ),
                        )
                    )
                )
            )
        );

        /** @var \Magento\Framework\View\File\Collector\Base $sourceBase */
        $sourceBase = $this->objectManager->create(
            'Magento\Framework\View\File\Collector\Base', array('filesystem' => $filesystem, 'subDir' => 'web')
        );
        /** @var \Magento\Framework\View\File\Collector\Base $sourceBase */
        $overriddenBaseFiles = $this->objectManager->create(
            'Magento\Framework\View\File\Collector\Override\Base', array('filesystem' => $filesystem, 'subDir' => 'web')
        );
        $this->model = $this->objectManager->create(
            'Magento\Framework\Less\File\Collector\Aggregated',
            array('baseFiles' => $sourceBase, 'overriddenBaseFiles' => $overriddenBaseFiles)
        );
    }

    /**
     * @magentoDataFixture Magento/Framework/Less/_files/themes.php
     * @magentoAppIsolation enabled
     * @magentoAppArea frontend
     * @param string $path
     * @param string $themeName
     * @param string[] $expectedFiles
     * @dataProvider getFilesDataProvider
     */
    public function testGetFiles($path, $themeName, array $expectedFiles)
    {
        /** @var \Magento\Framework\View\Design\Theme\FlyweightFactory $themeFactory */
        $themeFactory = $this->objectManager->get('Magento\Framework\View\Design\Theme\FlyweightFactory');
        $theme = $themeFactory->create($themeName);
        if (!count($expectedFiles)) {
            $this->setExpectedException('LogicException', 'magento_import returns empty result by path doesNotExist');
        }
        $files = $this->model->getFiles($theme, $path);
        $actualFiles = [];
        foreach ($files as $file) {
            $actualFiles[] = $file->getFilename();
        }
        $this->assertEquals($expectedFiles, $actualFiles);

        /** @var $file \Magento\Framework\View\File */
        foreach ($files as $file) {
            if (!in_array($file->getFilename(), $expectedFiles)) {
                $this->fail(sprintf('File "%s" is not expected but found', $file->getFilename()));
            }
        }
    }

    /**
     * @return array
     */
    public function getFilesDataProvider()
    {
        $fixtureDir = dirname(dirname(__DIR__));
        return array(
            'file in theme and parent theme' => array(
                '1.file',
                'test_default',
                array(
                    str_replace(
                        '\\',
                        '/',
                         "$fixtureDir/_files/design/frontend/test_default/web/1.file"
                    ),
                    str_replace(
                        '\\',
                        '/',
                        "$fixtureDir/_files/design/frontend/test_parent/Magento_Second/web/1.file"
                    ),
                    str_replace(
                        '\\',
                        '/',
                        "$fixtureDir/_files/design/frontend/test_default/Magento_Module/web/1.file"
                    ),
                )
            ),
            'file in library' => array(
                '2.file',
                'test_default',
                array(
                    str_replace(
                        '\\',
                        '/',
                        "$fixtureDir/_files/lib/web/2.file"
                    )
                )
            ),
            'non-existing file' => array(
                'doesNotExist',
                'test_default',
                array()
            ),
            'file in library, module, and theme' => array(
                '3.less',
                'test_default',
                array(
                    str_replace(
                        '\\',
                        '/',
                        "$fixtureDir/_files/lib/web/3.less"
                    ),
                    str_replace(
                        '\\',
                        '/',
                        "$fixtureDir/_files/code/Magento/Other/view/frontend/web/3.less"
                    ),
                    str_replace(
                        '\\',
                        '/',
                        "$fixtureDir/_files/design/frontend/test_default/Magento_Third/web/3.less"
                    )
                )
            ),
        );
    }
}
