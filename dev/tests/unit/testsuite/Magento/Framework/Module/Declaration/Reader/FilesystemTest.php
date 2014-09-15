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
namespace Magento\Framework\Module\Declaration\Reader;

class FilesystemTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Module\Declaration\Reader\Filesystem
     */
    protected $_model;

    protected function setUp()
    {
        $fileResolver = $this->getFileResolver(__DIR__ . '/../FileResolver/_files');
        $converter = new \Magento\Framework\Module\Declaration\Converter\Dom();
        $schemaLocatorMock = $this->getMock(
            'Magento\Framework\Module\Declaration\SchemaLocator',
            array(),
            array(),
            '',
            false
        );
        $validationStateMock = $this->getMock('Magento\Framework\Config\ValidationStateInterface');

        $dependencyManager = $this->getMock('Magento\Framework\Module\DependencyManagerInterface');
        $dependencyManager->expects(
            $this->any()
        )->method(
            'getExtendedModuleDependencies'
        )->will(
            $this->returnValue(array())
        );

        $this->_model = new \Magento\Framework\Module\Declaration\Reader\Filesystem(
            $fileResolver,
            $converter,
            $schemaLocatorMock,
            $validationStateMock,
            $dependencyManager
        );
    }

    public function testRead()
    {
        $expectedResult = array(
            'Module_One' => array(
                'name' => 'Module_One',
                'schema_version' => '1.0.0.0',
                'active' => true,
                'dependencies' => array(
                    'modules' => array(),
                    'extensions' => array(
                        'strict' => array(array('name' => 'simplexml')),
                        'alternatives' => array(
                            array(array('name' => 'gd'), array('name' => 'imagick', 'minVersion' => '3.0.0'))
                        )
                    )
                )
            ),
            'Module_Four' => array(
                'name' => 'Module_Four',
                'schema_version' => '1.0.0.0',
                'active' => true,
                'dependencies' => array(
                    'modules' => array('Module_One'),
                    'extensions' => array('strict' => array(), 'alternatives' => array())
                )
            ),
            'Module_Three' => array(
                'name' => 'Module_Three',
                'schema_version' => '1.0.0.0',
                'active' => true,
                'dependencies' => array(
                    'modules' => array('Module_Four'),
                    'extensions' => array('strict' => array(), 'alternatives' => array())
                )
            )
        );
        $this->assertEquals($expectedResult, $this->_model->read('global'));
    }

    /**
     * Get file resolver instance
     *
     * @param string $baseDir
     * @return \Magento\Framework\Module\Declaration\FileResolver
     */
    protected function getFileResolver($baseDir)
    {
        $filesystem = new \Magento\Framework\App\Filesystem(
            new \Magento\Framework\App\Filesystem\DirectoryList($baseDir),
            new \Magento\Framework\Filesystem\Directory\ReadFactory(),
            new \Magento\Framework\Filesystem\Directory\WriteFactory()
        );
        $iteratorFactory = new \Magento\Framework\Config\FileIteratorFactory();

        return new \Magento\Framework\Module\Declaration\FileResolver($filesystem, $iteratorFactory);
    }
}
