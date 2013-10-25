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
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
namespace Magento\Install\Model\EntryPoint;

class UpgradeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_indexer;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_config;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $_objectManager;

    protected function setUp()
    {
        $this->_config = $this->getMock('Magento\Core\Model\Config\Primary', array('getParam'), array(), '', false);

        $dirVerification = $this->getMock('Magento\App\Dir\Verification', array(), array(), '', false);

        $cacheFrontend = $this->getMockForAbstractClass('Magento\Cache\FrontendInterface');
        $cacheFrontend->expects($this->once())->method('clean')->with('all', array());
        $cacheFrontendPool = $this->getMock(
            'Magento\Core\Model\Cache\Frontend\Pool', array('valid', 'current'), array(
                $this->getMock('Magento\Core\Model\Cache\Frontend\Factory', array(), array(), '', false),
            )
        );
        $cacheFrontendPool->expects($this->at(0))->method('valid')->will($this->returnValue(true));
        $cacheFrontendPool->expects($this->once())->method('current')->will($this->returnValue($cacheFrontend));

        $update = $this->getMock(
            'Magento\App\Updater', array('updateScheme', 'updateData'), array(), '', false);
        $update->expects($this->once())->method('updateScheme');
        $update->expects($this->once())->method('updateData');

        $this->_indexer = $this->getMock(
            'Magento\Index\Model\Indexer', array('reindexAll', 'reindexRequired'), array(), '', false
        );

        $this->_objectManager = $this->getMock('Magento\ObjectManager');
        $this->_objectManager->expects($this->any())->method('get')->will($this->returnValueMap(array(
            array('Magento\Core\Model\Cache\Frontend\Pool', $cacheFrontendPool),
            array('Magento\App\Updater', $update),
            array('Magento\Core\Model\Config\Primary', $this->_config),
            array('Magento\Index\Model\Indexer', $this->_indexer),
            array('Magento\App\Dir\Verification', $dirVerification),
        )));
    }

    /**
     * @param string $reindexMode
     * @param int $reindexAllCount
     * @param int $reindexReqCount
     * @dataProvider processRequestDataProvider
     */
    public function testProcessRequest($reindexMode, $reindexAllCount, $reindexReqCount)
    {
        $this->_indexer->expects($this->exactly($reindexAllCount))->method('reindexAll');
        $this->_indexer->expects($this->exactly($reindexReqCount))->method('reindexRequired');
        $this->_config->expects($this->once())
            ->method('getParam')->with(\Magento\Install\Model\EntryPoint\Upgrade::REINDEX)
            ->will($this->returnValue($reindexMode));
        $upgrade = new \Magento\Install\Model\EntryPoint\Upgrade($this->_config, $this->_objectManager);
        $upgrade->processRequest();
    }

    public function processRequestDataProvider()
    {
        return array(
            'no reindex'       => array('', 0, 0),
            'reindex all'      => array(\Magento\Install\Model\EntryPoint\Upgrade::REINDEX_ALL, 1, 0),
            'reindex required' => array(\Magento\Install\Model\EntryPoint\Upgrade::REINDEX_INVALID, 0, 1),
        );
    }
}
