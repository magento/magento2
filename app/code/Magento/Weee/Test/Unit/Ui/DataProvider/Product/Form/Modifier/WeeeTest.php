<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Weee\Test\Unit\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Model\ResourceModel\Eav\Attribute as EavAttribute;
use Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory as EavAttributeFactory;
use Magento\Catalog\Test\Unit\Ui\DataProvider\Product\Form\Modifier\AbstractModifierTestCase;
use Magento\Directory\Model\Config\Source\Country as SourceCountry;
use Magento\Weee\Ui\DataProvider\Product\Form\Modifier\Manager\Website as WebsiteManager;
use Magento\Weee\Ui\DataProvider\Product\Form\Modifier\Weee;
use PHPUnit\Framework\MockObject\MockObject;

class WeeeTest extends AbstractModifierTestCase
{
    /**
     * @var SourceCountry|MockObject
     */
    protected $sourceCountryMock;

    /**
     * @var EavAttributeFactory|MockObject
     */
    protected $eavAttributeFactoryMock;

    /**
     * @var EavAttribute|MockObject
     */
    protected $eavAttributeMock;

    /**
     * @var WebsiteManager|MockObject
     */
    protected $websiteManagerMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sourceCountryMock = $this->getMockBuilder(SourceCountry::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->eavAttributeFactoryMock = $this->getMockBuilder(EavAttributeFactory::class)
            ->onlyMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->eavAttributeMock = $this->getMockBuilder(EavAttribute::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->websiteManagerMock = $this->getMockBuilder(WebsiteManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->eavAttributeFactoryMock->expects($this->any())
            ->method('create')
            ->willReturn($this->eavAttributeMock);
        $this->eavAttributeMock->expects($this->any())
            ->method('loadByCode')
            ->willReturn($this->eavAttributeMock);
    }

    /**
     * {@inheritdoc}
     */
    protected function createModel()
    {
        return $this->objectManager->getObject(Weee::class, [
            'locator' => $this->locatorMock,
            'sourceCountry' => $this->sourceCountryMock,
            'eavAttributeFactory' => $this->eavAttributeFactoryMock,
            'websiteManager' => $this->websiteManagerMock,
        ]);
    }

    public function testModifyMeta()
    {
        $this->assertSame([], $this->getModel()->modifyMeta([]));

        $this->assertNotEmpty($this->getModel()->modifyMeta([
            'weee_group' => [
                'children' => [
                    'weee_attribute' => [
                        'formElement' => Weee::FORM_ELEMENT_WEEE,
                    ],
                ],
            ],
        ]));
    }
}
