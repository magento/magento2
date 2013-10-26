<?php
/**
 * Include verification of overriding service call alias with different classes.
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
namespace Magento\Core\Model\DataService;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Core\Model\DataService\Config
     */
    protected $_config;

    protected function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        /** @var \Magento\App\Dir $dirs */
        $dirs = $objectManager->create(
            'Magento\App\Dir',
            array(
                'baseDir' => BP,
                'dirs' => array(
                    \Magento\App\Dir::MODULES => __DIR__ . '/LayoutTest',
                    \Magento\App\Dir::CONFIG => __DIR__ . '/LayoutTest',
                )
            )
        );

        $moduleList = $objectManager->create(
            'Magento\App\ModuleList',
            array(
                'reader' => $objectManager->create(
                    'Magento\App\Module\Declaration\Reader\Filesystem',
                    array(
                        'fileResolver' => $objectManager->create(
                            'Magento\App\Module\Declaration\FileResolver',
                            array(
                                'applicationDirs' => $dirs
                            )
                        )
                    )
                ),
                'cache' => $this->getMock('Magento\Config\CacheInterface')
            )
        );

        /** @var \Magento\Core\Model\Config\Modules\Reader $moduleReader */
        $moduleReader = $objectManager->create(
            'Magento\Core\Model\Config\Modules\Reader',
            array(
                'moduleList' => $moduleList
            )
        );
        $moduleReader->setModuleDir('Magento_Last', 'etc', __DIR__ . '/LayoutTest/Magento/Last/etc');

        /** @var \Magento\Core\Model\DataService\Config\Reader\Factory $dsCfgReaderFactory */
        $dsCfgReaderFactory = $objectManager->create('Magento\Core\Model\DataService\Config\Reader\Factory');

        $this->_config = new \Magento\Core\Model\DataService\Config($dsCfgReaderFactory, $moduleReader);
    }

    public function testGetClassByAliasOverride()
    {
        $classInfo = $this->_config->getClassByAlias('alias');
        $this->assertEquals('last_service', $classInfo['class']);
        $this->assertEquals('last_method', $classInfo['retrieveMethod']);
        $this->assertEquals('last_value', $classInfo['methodArguments']['last_arg']);
        $this->assertEquals('last_value_two', $classInfo['methodArguments']['last_arg_two']);
    }
}
