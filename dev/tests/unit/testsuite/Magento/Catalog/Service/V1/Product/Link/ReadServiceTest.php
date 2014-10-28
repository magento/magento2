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

namespace Magento\Catalog\Service\V1\Product\Link;

use Magento\Catalog\Service\V1\Product\Link\Data\LinkType;
use Magento\Catalog\Service\V1\Product\Link\Data\ProductLink;

class ReadServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ReadService
     */
    protected $service;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $providerMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $builderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productBuilderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $productLoaderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $collectionProviderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $linkFactoryMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $linkBuilderMock;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $linkResolverMock;

    protected function setUp()
    {
        $helper = new \Magento\TestFramework\Helper\ObjectManager($this);

        $this->providerMock = $this->getMock('Magento\Catalog\Model\Product\LinkTypeProvider', [], [], '', false);
        $this->builderMock = $this->getMock(
            'Magento\Catalog\Service\V1\Product\Link\Data\LinkTypeBuilder',
            [],
            [],
            '',
            false
        );
        $this->productBuilderMock = $this->getMock(
            'Magento\Catalog\Service\V1\Product\Link\Data\ProductLinkBuilder',
            [],
            [],
            '',
            false
        );

        $this->productLoaderMock = $this->getMock(
            'Magento\Catalog\Service\V1\Product\ProductLoader',
            [],
            [],
            '',
            false
        );

        $this->collectionProviderMock = $this->getMock(
            'Magento\Catalog\Service\V1\Product\Link\Data\ProductLink\CollectionProvider',
            [],
            [],
            '',
            false
        );

        $this->linkFactoryMock = $this->getMock(
            'Magento\Catalog\Model\Product\LinkFactory',
            ['create'],
            [],
            '',
            false
        );

        $this->linkBuilderMock = $this->getMock(
            'Magento\Catalog\Service\V1\Product\Link\Data\LinkAttributeBuilder',
            [],
            [],
            '',
            false
        );

        $this->linkResolverMock = $this->getMock(
            'Magento\Catalog\Service\V1\Product\Link\LinkTypeResolver',
            [],
            [],
            '',
            false
        );

        $this->service = $helper->getObject(
            'Magento\Catalog\Service\V1\Product\Link\ReadService',
            [
                'linkTypeProvider' => $this->providerMock,
                'builder' => $this->builderMock,
                'productLoader'  => $this->productLoaderMock,
                'productEntityBuilder' => $this->productBuilderMock,
                'entityCollectionProvider' => $this->collectionProviderMock,
                'linkFactory' => $this->linkFactoryMock,
                'linkAttributeBuilder' => $this->linkBuilderMock,
                'linkTypeResolver' => $this->linkResolverMock

            ]
        );
    }

    public function testGetProductLinkTypes()
    {
        $types = ['typeOne' => 'codeOne', 'typeTwo' => 'codeTwo'];

        $this->providerMock->expects($this->once())->method('getLinkTypes')->will($this->returnValue($types));

        $this->builderMock->expects($this->exactly(2))
            ->method('populateWithArray')
            ->with(
                $this->logicalOr(
                    $this->equalTo([LinkType::TYPE => 'typeOne', LinkType::CODE => 'codeOne']),
                    $this->equalTo([LinkType::TYPE => 'typeTwo', LinkType::CODE => 'codeTwo'])
                )
            )->will($this->returnSelf());

        $this->builderMock->expects($this->exactly(2))
            ->method('create')
            ->will($this->returnSelf());

        $this->assertCount(2, $this->service->getProductLinkTypes());
    }

    public function testGetLinkedProducts()
    {
        $productMock = $this->getMock('Magento\Catalog\Model\Product', array(), array(), '', false);
        $this->productLoaderMock
            ->expects($this->once())->method('load')
            ->with('product_sku')
            ->will($this->returnValue($productMock));
        $itemMock = [
            ProductLink::TYPE => 'typeId',
            ProductLink::SKU => 'sku',
            ProductLink::POSITION => 0
        ];
        $this->collectionProviderMock
            ->expects($this->once())
            ->method('getCollection')
            ->with($productMock, 'productType')
            ->will($this->returnValue(array($itemMock)));

        $this->productBuilderMock
            ->expects($this->once())
            ->method('populateWithArray')
            ->with($itemMock)
            ->will($this->returnSelf());
        $this->productBuilderMock->expects($this->once())->method('create')->will($this->returnValue('Expected'));
        $this->assertequals(array('Expected'), $this->service->getLinkedProducts('product_sku', 'productType'));
    }

    public function testGetLinkedAttributes()
    {
        $linkMock = $this->getMock('Magento\Catalog\Model\Product\Link', array(), array(), '', false);
        $attributeMock = [['code' => 'code_name', 'type' => 'type_name']];
        $this->linkResolverMock
            ->expects($this->once())
            ->method('getTypeIdByCode')
            ->with('productType')
            ->will($this->returnValue('type_id'));
        $this->linkFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with(['data' => ['link_type_id' => 'type_id']])
            ->will($this->returnValue($linkMock));
        $data = [
            Data\LinkAttribute::CODE => 'code_name',
            Data\LinkAttribute::TYPE => 'type_name'
        ];
        $linkMock->expects($this->once())->method('getAttributes')->will($this->returnValue($attributeMock));
        $this->linkBuilderMock
            ->expects($this->once())
            ->method('populateWithArray')
            ->with($data)
            ->will($this->returnSelf());
        $this->linkBuilderMock->expects($this->once())->method('create')->will($this->returnValue('Expected'));
        $this->assertequals(array('Expected'), $this->service->getLinkAttributes('productType'));
    }
}
