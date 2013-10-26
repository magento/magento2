<?php
/**
 * \Magento\Core\Model\Fieldset\Config\Reader
 *
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
namespace Magento\Core\Model\Fieldset\Config;

/**
 * @magentoDataFixture Magento/Adminhtml/controllers/_files/cache/all_types_disabled.php
 */
class ReaderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Core\Model\Fieldset\Config\Reader
     */
    protected $_model;

    public function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var \Magento\App\Dir $dirs */
        $dirs = $objectManager->create(
            'Magento\App\Dir', array(
                'baseDir' => BP,
                'dirs' => array(
                    \Magento\App\Dir::MODULES => __DIR__ . '/_files',
                    \Magento\App\Dir::CONFIG => __DIR__ . '/_files'
                )
            )
        );

        /** @var \Magento\App\Module\Declaration\FileResolver $modulesDeclarations */
        $modulesDeclarations = $objectManager->create(
            'Magento\App\Module\Declaration\FileResolver', array(
                'applicationDirs' => $dirs,
            )
        );


        /** @var \Magento\App\Module\Declaration\Reader\Filesystem $filesystemReader */
        $filesystemReader = $objectManager->create(
            'Magento\App\Module\Declaration\Reader\Filesystem', array(
                'fileResolver' => $modulesDeclarations,
            )
        );

        /** @var \Magento\App\ModuleList $modulesList */
        $modulesList = $objectManager->create(
            'Magento\App\ModuleList', array(
                'reader' => $filesystemReader,
            )
        );

        /** @var \Magento\Core\Model\Config\Modules\Reader $moduleReader */
        $moduleReader = $objectManager->create(
            'Magento\Core\Model\Config\Modules\Reader', array(
                'moduleList' => $modulesList
            )
        );
        $moduleReader->setModuleDir('Magento_Test', 'etc', __DIR__ . '/_files/Magento/Test/etc');

        /** @var \Magento\Core\Model\Config\FileResolver $fileResolver */
        $fileResolver = $objectManager->create(
            'Magento\Core\Model\Config\FileResolver', array(
                'moduleReader' => $moduleReader,
            )
        );

        $this->_model = $objectManager->create(
            'Magento\Core\Model\Fieldset\Config\Reader', array(
                'fileResolver' => $fileResolver,
            )
        );
    }

    public function testRead()
    {
        $result = $this->_model->read('global');
        $expected = include '_files/expectedArray.php';
        $this->assertEquals($expected, $result);
    }

    public function testMergeCompleteAndPartial()
    {
        $fileList = array(
            __DIR__ . '/_files/partialFieldsetFirst.xml',
            __DIR__ . '/_files/partialFieldsetSecond.xml'
        );
        $fileResolverMock = $this->getMockBuilder('Magento\Config\FileResolverInterface')
            ->setMethods(array('get'))
            ->disableOriginalConstructor()
            ->getMock();
        $fileResolverMock->expects($this->once())
            ->method('get')
            ->with($this->equalTo('fieldset.xml'), $this->equalTo('global'))
            ->will($this->returnValue($fileList));

        /** @var \Magento\Core\Model\Fieldset\Config\Reader $model */
        $model = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Core\Model\Fieldset\Config\Reader', array(
                'fileResolver' => $fileResolverMock,
            )
        );
        $expected = array(
            'global' => array(
                'sales_convert_quote_item' => array(
                    'event_id' => array(
                        'to_order_item' => "*",
                    ),
                    'event_name' => array(
                        'to_order_item' => "*"
                    ),
                    'event_description' => array(
                        'to_order_item' => "complexDesciption"
                    )
                )
            )
        );
        $this->assertEquals($expected, $model->read('global'));
    }
}
