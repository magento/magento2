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
 * @subpackage  integration_tests
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\View;

class RelatedFileTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var RelatedFile
     */
    protected $model;

    public function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->model = $objectManager->get('Magento\View\RelatedFile');
    }

    /**
     * @dataProvider buildPathDataProvider
     */
    public function testBuildPath($arguments, $expected)
    {
        $path = $this->model->buildPath(
            $arguments['relatedFilePath'],
            $arguments['parentRelativePath'],
            $arguments['params']
        );
        $this->assertEquals($expected['path'], $path);
        $this->assertEquals($expected['params'], $arguments['params']);
    }

    /**
     * @return array
     */
    public function buildPathDataProvider()
    {
        return array(
            array(
                'arguments' => array(
                    'relatedFilePath' => '../directory/file.css',
                    'parentRelativePath' => 'css/source.css',
                    'params' => ['module' => false]
                ),
                'expected' => array(
                    'path' => 'directory/file.css',
                    'params' => ['module' => false]
                )
            ),
            array(
                'arguments' => array(
                    'relatedFilePath' => '../some_dir/file.css',
                    'parentRelativePath' => 'css/source.css',
                    'params' => ['module' => 'Magento_Theme']
                ),
                'expected' => array(
                    'path' => 'some_dir/file.css',
                    'params' => ['module' => 'Magento_Theme']
                )
            ),
            array(
                'arguments' => array(
                    'relatedFilePath' => 'Magento_Theme::some_dir/file.css',
                    'parentRelativePath' => 'css/source.css',
                    'params' => ['module' => false]
                ),
                'expected' => array(
                    'path' => 'some_dir/file.css',
                    'params' => ['module' => 'Magento_Theme']
                )
            )
        );
    }
}
