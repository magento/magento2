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
namespace Magento\Framework\Model\Resource\Db\Collection;

class AbstractTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Framework\Model\Resource\Db\Collection\AbstractCollection
     */
    protected $_model = null;

    protected function setUp()
    {
        $resourceModel = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get('Magento\Framework\App\Resource');
        $resource = $this->getMockForAbstractClass(
            'Magento\Framework\Model\Resource\Db\AbstractDb',
            array($resourceModel),
            '',
            true,
            true,
            true,
            array('getMainTable', 'getIdFieldName')
        );

        $resource->expects(
            $this->any()
        )->method(
            'getMainTable'
        )->will(
            $this->returnValue($resource->getTable('store_website'))
        );
        $resource->expects($this->any())->method('getIdFieldName')->will($this->returnValue('website_id'));

        $fetchStrategy = $this->getMockForAbstractClass('Magento\Framework\Data\Collection\Db\FetchStrategyInterface');

        $eventManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\Event\ManagerInterface'
        );

        $entityFactory = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Core\Model\EntityFactory'
        );
        $logger = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get('Magento\Framework\Logger');

        $this->_model = $this->getMockForAbstractClass(
            'Magento\Framework\Model\Resource\Db\Collection\AbstractCollection',
            array($entityFactory, $logger, $fetchStrategy, $eventManager, null, $resource)
        );
    }

    public function testGetAllIds()
    {
        $allIds = $this->_model->getAllIds();
        sort($allIds);
        $this->assertEquals(array('0', '1'), $allIds);
    }

    public function testGetAllIdsWithBind()
    {
        $this->_model->getSelect()->where('code = :code');
        $this->_model->addBindParam('code', 'admin');
        $this->assertEquals(array('0'), $this->_model->getAllIds());
    }
}
