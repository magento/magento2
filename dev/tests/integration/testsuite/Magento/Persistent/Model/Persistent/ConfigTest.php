<?php
/**
 * \Magento\Persistent\Model\Persistent\Config
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
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Persistent\Model\Persistent;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Persistent\Model\Persistent\Config
     */
    protected $_model;

    /** @var  \Magento\Framework\ObjectManager */
    protected $_objectManager;

    public function setUp()
    {
        $directoryList = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Framework\App\Filesystem\DirectoryList',
            array(
                'root' => \Magento\Framework\App\Filesystem::ROOT_DIR,
                'directories' => array(
                    \Magento\Framework\App\Filesystem::MODULES_DIR => array('path' => dirname(__DIR__))
                )
            )
        );
        $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Framework\App\Filesystem',
            array('directoryList' => $directoryList)
        );

        $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->_model = $this->_objectManager->create(
            'Magento\Persistent\Model\Persistent\Config',
            array('filesystem' => $filesystem)
        );
    }

    public function testCollectInstancesToEmulate()
    {
        $this->_model->setConfigFilePath(__DIR__ . '/_files/persistent.xml');
        $result = $this->_model->collectInstancesToEmulate();
        $expected = include '_files/expectedArray.php';
        $this->assertEquals($expected, $result);
    }

    public function testGetBlockConfigInfo()
    {
        $this->_model->setConfigFilePath(__DIR__ . '/_files/persistent.xml');
        $blocks = $this->_model->getBlockConfigInfo('Magento\Sales\Block\Reorder\Sidebar');
        $expected = include '_files/expectedBlocksArray.php';
        $this->assertEquals($expected, $blocks);
    }

    public function testGetBlockConfigInfoNotConfigured()
    {
        $this->_model->setConfigFilePath(__DIR__ . '/_files/persistent.xml');
        $blocks = $this->_model->getBlockConfigInfo('Magento\Catalog\Block\Product\Compare\ListCompare');
        $this->assertEquals(array(), $blocks);
    }
}
