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
namespace Magento\TestFramework\Dependency;

class LayoutRuleTest extends \PHPUnit_Framework_TestCase
{
    public function testNonLayoutGetDependencyInfo()
    {
        $model = new LayoutRule(array(), array(), array());
        $content = 'any content';
        $this->assertEmpty($model->getDependencyInfo('any', 'not layout', 'any', $content));
    }

    /**
     * @param string $contents
     * @param array $expected
     * @dataProvider getDependencyInfoDataProvider
     */
    public function testGetDependencyInfo($contents, array $expected)
    {
        $model = new LayoutRule(array(), array(), array());
        $this->assertEquals($expected, $model->getDependencyInfo('Magento\SomeModule', 'layout', 'any', $contents));
    }

    public function getDependencyInfoDataProvider()
    {
        return array(
            array(
                '<element module="Magento\AnotherModule"/>',
                array(
                    array(
                        'module' => 'Magento\AnotherModule',
                        'type' => \Magento\Test\Integrity\DependencyTest::TYPE_SOFT,
                        'source' => '<element module="Magento\AnotherModule"/>'
                    )
                )
            ),
            array('<element module="Magento\SomeModule"/>', array()),
            array(
                '<block class="Magento\AnotherModule\Several\Chunks"/>',
                array(
                    array(
                        'module' => 'Magento\AnotherModule',
                        'type' => \Magento\Test\Integrity\DependencyTest::TYPE_HARD,
                        'source' => '<block class="Magento\AnotherModule\Several\Chunks"/>'
                    )
                )
            ),
            array('<block class="Magento\SomeModule\Several\Chunks"/>', array()),
            array(
                '<any>
                    <extra></extra><block template="Magento_AnotherModule::template/path.phtml"/>
                </any>',
                array(
                    array(
                        'module' => 'Magento\AnotherModule',
                        'type' => \Magento\Test\Integrity\DependencyTest::TYPE_SOFT,
                        'source' => '<block template="Magento_AnotherModule::template/path.phtml"/>'
                    )
                )
            ),
            array('<block template="Magento_SomeModule::template/path.phtml"/>', array()),
            array(
                '<block>Magento\AnotherModule\Several\Chunks</block>',
                array(
                    array(
                        'module' => 'Magento\AnotherModule',
                        'type' => \Magento\Test\Integrity\DependencyTest::TYPE_SOFT,
                        'source' => '<block>Magento\AnotherModule\Several\Chunks</block>'
                    )
                )
            ),
            array('<block>Magento\SomeModule\Several\Chunks</block>', array()),
            array(
                '<template>Magento_AnotherModule::template/path.phtml</template>',
                array(
                    array(
                        'module' => 'Magento\AnotherModule',
                        'type' => \Magento\Test\Integrity\DependencyTest::TYPE_SOFT,
                        'source' => '<template>Magento_AnotherModule::template/path.phtml</template>'
                    )
                )
            ),
            array('<template>Magento_SomeModule::template/path.phtml</template>', array()),
            array(
                '<file>Magento_AnotherModule::file/path.txt</file>',
                array(
                    array(
                        'module' => 'Magento\AnotherModule',
                        'type' => \Magento\Test\Integrity\DependencyTest::TYPE_SOFT,
                        'source' => '<file>Magento_AnotherModule::file/path.txt</file>'
                    )
                )
            ),
            array('<file>Magento_SomeModule::file/path.txt</file>', array()),
            array(
                '<any helper="Magento\AnotherModule\Several\Chunks::text"/>',
                array(
                    array(
                        'module' => 'Magento\AnotherModule',
                        'type' => \Magento\Test\Integrity\DependencyTest::TYPE_SOFT,
                        'source' => '<any helper="Magento\AnotherModule\Several\Chunks::text"/>'
                    )
                )
            ),
            array('<any helper="Magento\SomeModule\Several\Chunks::text"/>', array())
        );
    }

    /**
     * @param string $contents
     * @param string $type
     * @dataProvider layoutGetDependencyInfoDataProvider
     */
    public function testUpdatesRouterGetDependencyInfo($contents, $type)
    {
        $model = new LayoutRule(array('router_name' => array('Magento\RouterModule')), array(), array());
        $this->assertEquals(array(), $model->getDependencyInfo('Magento\RouterModule', 'layout', 'any', $contents));
        $this->assertEquals(
            array(array('module' => 'Magento\RouterModule', 'type' => $type, 'source' => 'router_name_action')),
            $model->getDependencyInfo('Magento\AnotherModule', 'layout', 'any', $contents)
        );
    }

    /**
     * @param string $contents
     * @param string $type
     * @param bool $isHandle
     * @dataProvider layoutGetDependencyInfoWithReferenceDataProvider
     */
    public function testLayoutGetDependencyInfo($contents, $type, $isHandle)
    {
        // test one module
        $data = array(
            'frontend' => array('any_handle_name' => array('Magento\AnyHandleModule' => 'Magento\AnyHandleModule')),
            'default' => array('singlechunk' => array('Magento\DefaultHandleModule' => 'Magento\DefaultHandleModule'))
        );
        $model = $isHandle ? new LayoutRule(array(), array(), $data) : new LayoutRule(array(), $data, array());
        $this->assertEquals(
            array(),
            $model->getDependencyInfo('Magento\AnyHandleModule', 'layout', 'path/frontend/file.txt', $contents)
        );
        $this->assertEquals(
            array(),
            $model->getDependencyInfo('Magento\DefaultHandleModule', 'layout', 'any', $contents)
        );
        $this->assertEquals(
            array(array('module' => 'Magento\DefaultHandleModule', 'type' => $type, 'source' => 'singlechunk')),
            $model->getDependencyInfo('any', 'layout', 'any', $contents)
        );
        $this->assertEquals(
            array(array('module' => 'Magento\AnyHandleModule', 'type' => $type, 'source' => 'any_handle_name')),
            $model->getDependencyInfo('any', 'layout', 'path/frontend/file.txt', $contents)
        );
        // test several modules
        $data = array(
            'frontend' => array(
                'any_handle_name' => array(
                    'Magento\Theme' => 'Magento\Theme',
                    'Magento\HandleModule' => 'Magento\HandleModule'
                )
            )
        );
        $model = $isHandle ? new LayoutRule(array(), array(), $data) : new LayoutRule(array(), $data, array());
        $this->assertEquals(
            array(array('module' => 'Magento\Theme', 'type' => $type, 'source' => 'any_handle_name')),
            $model->getDependencyInfo('any', 'layout', 'path/frontend/file.txt', $contents)
        );
        $this->assertEquals(
            array(),
            $model->getDependencyInfo('Magento\HandleModule', 'layout', 'path/frontend/file.txt', $contents)
        );
    }

    public function layoutGetDependencyInfoDataProvider()
    {
        return array(
            array(
                $this->_getLayoutFileContent('layout_handle.xml'),
                \Magento\Test\Integrity\DependencyTest::TYPE_SOFT,
                true
            ),
            array(
                $this->_getLayoutFileContent('layout_handle_parent.xml'),
                \Magento\Test\Integrity\DependencyTest::TYPE_HARD,
                true
            ),
            array(
                $this->_getLayoutFileContent('layout_handle_update.xml'),
                \Magento\Test\Integrity\DependencyTest::TYPE_SOFT,
                true
            )
        );
    }

    public function layoutGetDependencyInfoWithReferenceDataProvider()
    {
        return array_merge(
            $this->layoutGetDependencyInfoDataProvider(),
            array(
                array(
                    $this->_getLayoutFileContent('layout_reference.xml'),
                    \Magento\Test\Integrity\DependencyTest::TYPE_SOFT,
                    false
                )
            )
        );
    }

    /**
     * Get content of layout file
     *
     * @param string $fileName
     * @return string
     */
    protected function _getLayoutFileContent($fileName)
    {
        return file_get_contents(str_replace('\\', '/', realpath(__DIR__)) . '/_files/' . $fileName);
    }
}
