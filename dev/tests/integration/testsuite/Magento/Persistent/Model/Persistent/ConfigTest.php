<?php
/**
 * \Magento\Persistent\Model\Persistent\Config
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Persistent\Model\Persistent;

use Magento\Framework\App\Filesystem\DirectoryList;

class ConfigTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Persistent\Model\Persistent\Config
     */
    protected $_model;

    /** @var  \Magento\Framework\ObjectManagerInterface */
    protected $_objectManager;

    public function setUp()
    {
        $directoryList = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Framework\App\Filesystem\DirectoryList',
            ['root' => DirectoryList::ROOT]
        );
        $filesystem = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Framework\Filesystem',
            ['directoryList' => $directoryList]
        );

        $this->_objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->_model = $this->_objectManager->create(
            'Magento\Persistent\Model\Persistent\Config',
            ['filesystem' => $filesystem]
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
        $this->assertEquals([], $blocks);
    }
}
