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

namespace Magento\Catalog\Model\Resource\Product\Link\Product;

use Magento\TestFramework\Helper\ObjectManager as ObjectManagerHelper;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class CollectionTest extends \PHPUnit_Framework_TestCase
{
    /** @var \Magento\Catalog\Model\Resource\Product\Link\Product\Collection */
    protected $collection;

    /** @var ObjectManagerHelper */
    protected $objectManagerHelper;

    /** @var \Magento\Core\Model\EntityFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $entityFactoryMock;

    /** @var \Magento\Logger|\PHPUnit_Framework_MockObject_MockObject */
    protected $loggerMock;

    /** @var \Magento\Data\Collection\Db\FetchStrategyInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $fetchStrategyMock;

    /** @var \Magento\Event\ManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $managerInterfaceMock;

    /** @var \Magento\Eav\Model\Config|\PHPUnit_Framework_MockObject_MockObject */
    protected $configMock;

    /** @var \Magento\Framework\App\Resource|\PHPUnit_Framework_MockObject_MockObject */
    protected $resourceMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $entityFactoryMock2;

    /** @var \Magento\Catalog\Model\Resource\Helper|\PHPUnit_Framework_MockObject_MockObject */
    protected $helperMock;

    /** @var \Magento\Validator\UniversalFactory|\PHPUnit_Framework_MockObject_MockObject */
    protected $universalFactoryMock;

    /** @var \Magento\Framework\StoreManagerInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $storeManagerMock;

    /** @var \Magento\Catalog\Helper\Data|\PHPUnit_Framework_MockObject_MockObject */
    protected $catalogHelperMock;

    /** @var \Magento\Catalog\Model\Indexer\Product\Flat\State|\PHPUnit_Framework_MockObject_MockObject */
    protected $stateMock;

    /** @var \Magento\Framework\App\Config\ScopeConfigInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $scopeConfigInterfaceMock;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    protected $optionFactoryMock;

    /** @var \Magento\Catalog\Model\Resource\Url|\PHPUnit_Framework_MockObject_MockObject */
    protected $urlMock;

    /** @var \Magento\Stdlib\DateTime\TimezoneInterface|\PHPUnit_Framework_MockObject_MockObject */
    protected $timezoneInterfaceMock;

    /** @var \Magento\Customer\Model\Session|\PHPUnit_Framework_MockObject_MockObject */
    protected $sessionMock;

    /** @var \Magento\Stdlib\DateTime|\PHPUnit_Framework_MockObject_MockObject */
    protected $dateTimeMock;

    protected function setUp()
    {
        $this->entityFactoryMock = $this->getMock('Magento\Core\Model\EntityFactory', [], [], '', false);
        $this->loggerMock = $this->getMock('Magento\Framework\Logger', [], [], '', false);
        $this->fetchStrategyMock = $this->getMock('Magento\Framework\Data\Collection\Db\FetchStrategyInterface');
        $this->managerInterfaceMock = $this->getMock('Magento\Framework\Event\ManagerInterface');
        $this->configMock = $this->getMock('Magento\Eav\Model\Config', [], [], '', false);
        $this->resourceMock = $this->getMock('Magento\Framework\App\Resource', [], [], '', false);
        $this->entityFactoryMock2 = $this->getMock('Magento\Eav\Model\EntityFactory');
        $this->helperMock = $this->getMock('Magento\Catalog\Model\Resource\Helper', [], [], '', false);
        $entity = $this->getMock('Magento\Eav\Model\Entity\AbstractEntity', [], [], '', false);
        $adapter = $this->getMockForAbstractClass('Zend_Db_Adapter_Abstract', [], '', false);
        $entity->expects($this->any())->method('getReadConnection')->will($this->returnValue($adapter));
        $entity->expects($this->any())->method('getDefaultAttributes')->will($this->returnValue([]));
        $this->universalFactoryMock = $this->getMock('Magento\Framework\Validator\UniversalFactory', [], [], '', false);
        $this->universalFactoryMock->expects($this->any())->method('create')->will($this->returnValue($entity));
        $this->storeManagerMock = $this->getMockForAbstractClass('Magento\Framework\StoreManagerInterface');
        $this->storeManagerMock
            ->expects($this->any())
            ->method('getStore')
            ->will($this->returnCallback(
                function ($store) {
                    return is_object($store) ? $store : new \Magento\Framework\Object(array('id' => 42));
                }
            ));
        $this->catalogHelperMock = $this->getMock('Magento\Catalog\Helper\Data', [], [], '', false);
        $this->stateMock = $this->getMock('Magento\Catalog\Model\Indexer\Product\Flat\State', [], [], '', false);
        $this->scopeConfigInterfaceMock = $this->getMock('Magento\Framework\App\Config\ScopeConfigInterface');
        $this->optionFactoryMock = $this->getMock('Magento\Catalog\Model\Product\OptionFactory');
        $this->urlMock = $this->getMock('Magento\Catalog\Model\Resource\Url', [], [], '', false);
        $this->timezoneInterfaceMock = $this->getMock('Magento\Framework\Stdlib\DateTime\TimezoneInterface');
        $this->sessionMock = $this->getMock('Magento\Customer\Model\Session', [], [], '', false);
        $this->dateTimeMock = $this->getMock('Magento\Framework\Stdlib\DateTime');
        $this->objectManagerHelper = new ObjectManagerHelper($this);
        $this->collection = $this->objectManagerHelper->getObject(
            'Magento\Catalog\Model\Resource\Product\Link\Product\Collection',
            [
                'entityFactory' => $this->entityFactoryMock,
                'logger' => $this->loggerMock,
                'fetchStrategy' => $this->fetchStrategyMock,
                'eventManager' => $this->managerInterfaceMock,
                'eavConfig' => $this->configMock,
                'resource' => $this->resourceMock,
                'eavEntityFactory' => $this->entityFactoryMock2,
                'resourceHelper' => $this->helperMock,
                'universalFactory' => $this->universalFactoryMock,
                'storeManager' => $this->storeManagerMock,
                'catalogData' => $this->catalogHelperMock,
                'catalogProductFlatState' => $this->stateMock,
                'scopeConfig' => $this->scopeConfigInterfaceMock,
                'productOptionFactory' => $this->optionFactoryMock,
                'catalogUrl' => $this->urlMock,
                'localeDate' => $this->timezoneInterfaceMock,
                'customerSession' => $this->sessionMock,
                'dateTime' => $this->dateTimeMock
            ]
        );
    }

    public function testSetProduct()
    {
        /** @var \Magento\Catalog\Model\Product|\PHPUnit_Framework_MockObject_MockObject $product */
        $product = $this->getMock('Magento\Catalog\Model\Product', [], [], '', false);
        $product->expects($this->any())->method('getId')->will($this->returnValue('5'));
        $productStore = new \Magento\Framework\Object(array('id' => 33));
        $product->expects($this->any())->method('getStore')->will($this->returnValue($productStore));
        $this->collection->setProduct($product);
        $this->assertEquals(33, $this->collection->getStoreId());
    }
}
