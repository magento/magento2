<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Test\Unit\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\Attributes;
use Magento\Framework\AuthorizationInterface;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;

/**
 * Class AttributesTest
 */
class AttributesTest extends AbstractModifierTest
{
    /**
     * @var UrlInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $urlBuilderMock;

    /**
     * @var Registry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $registryMock;

    /**
     * @var AuthorizationInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $authorizationMock;

    protected function setUp()
    {
        parent::setUp();
        $this->urlBuilderMock = $this->getMockBuilder(UrlInterface::class)
            ->getMockForAbstractClass();
        $this->registryMock = $this->getMockBuilder(Registry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->authorizationMock = $this->getMockBuilder(AuthorizationInterface::class)
            ->getMockForAbstractClass();
    }

    /**
     * {@inheritdoc}
     */
    protected function createModel()
    {
        return $this->objectManager->getObject(Attributes::class, [
            'urlBuilder' => $this->urlBuilderMock,
            'registry' => $this->registryMock,
            'authorization' => $this->authorizationMock,
            'locator' => $this->locatorMock,
        ]);
    }

    public function testModifyData()
    {
        $this->assertSame($this->getSampleData(), $this->getModel()->modifyData($this->getSampleData()));
    }

    public function testModifyMeta()
    {
        $this->registryMock->expects($this->once())
            ->method('registry')
            ->with('use_wrapper')
            ->willReturn(true);
        $this->authorizationMock->expects($this->once())
            ->method('isAllowed')
            ->with('Magento_Catalog::attributes_attributes')
            ->willReturn(true);

        $this->assertArrayHasKey('add_attribute_modal', $this->getModel()->modifyMeta([]));
    }
}
