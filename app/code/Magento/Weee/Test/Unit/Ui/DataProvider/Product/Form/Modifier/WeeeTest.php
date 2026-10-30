<?php
/**
 * Copyright 2016 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Weee\Test\Unit\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Model\ResourceModel\Eav\Attribute as EavAttribute;
use Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory as EavAttributeFactory;
use Magento\Catalog\Test\Unit\Ui\DataProvider\Product\Form\Modifier\AbstractModifierTestCase;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Directory\Model\Config\Source\Country as SourceCountry;
use Magento\Weee\Ui\DataProvider\Product\Form\Modifier\Manager\Website as WebsiteManager;
use Magento\Weee\Ui\DataProvider\Product\Form\Modifier\Weee;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * Unit Tests to cover Weee
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.UnusedLocalVariable)
 */
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
        $this->sourceCountryMock = $this->createMock(SourceCountry::class);
        $this->eavAttributeFactoryMock = $this->createPartialMock(EavAttributeFactory::class, ['create']);
        $this->eavAttributeMock = $this->createMock(EavAttribute::class);
        $this->websiteManagerMock = $this->createMock(WebsiteManager::class);

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
        return new Weee(
            $this->locatorMock,
            $this->sourceCountryMock,
            $this->createMock(DirectoryHelper::class),
            $this->eavAttributeFactoryMock,
            $this->websiteManagerMock
        );
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
