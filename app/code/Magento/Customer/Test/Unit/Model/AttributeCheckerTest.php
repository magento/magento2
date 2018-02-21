<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Customer\Test\Unit\Model;

use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Customer\Api\AddressMetadataManagementInterface;
use Magento\Customer\Api\Data\AttributeMetadataInterface;
use Magento\Customer\Model\AttributeChecker;
use Magento\Customer\Model\Attribute;
use Magento\Customer\Model\Metadata\AttributeResolver;

class AttributeCheckerTest extends \PHPUnit\Framework\TestCase
{
    /** @var AttributeChecker|\PHPUnit_Framework_MockObject_MockObject */
    private $model;

    /** @var  AttributeResolver|\PHPUnit_Framework_MockObject_MockObject */
    private $attributeResolver;

    /** @var AddressMetadataInterface|\PHPUnit_Framework_MockObject_MockObject */
    private $addressMetadataService;

    protected function setUp()
    {
        $this->addressMetadataService = $this->getMockForAbstractClass(AddressMetadataInterface::class);
        $this->attributeResolver = $this->getMockBuilder(AttributeResolver::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->model = new AttributeChecker(
            $this->addressMetadataService,
            $this->attributeResolver
        );
    }

    /**
     * @param bool $isAllowed
     * @param bool $isMetadataExists
     * @param string $attributeCode
     * @param string $formName
     * @param array $attributeFormsList
     *
     * @dataProvider attributeOnFormDataProvider
     */
    public function testIsAttributeAllowedOnForm(
        $isAllowed,
        $isMetadataExists,
        $attributeCode,
        $formName,
        array $attributeFormsList
    ) {
        $attributeMetadata = null;
        if ($isMetadataExists) {
            $attributeMetadata = $this->getMockForAbstractClass(AttributeMetadataInterface::class);
            $attribute = $this->getMockBuilder(Attribute::class)
                ->disableOriginalConstructor()
                ->getMock();
            $this->attributeResolver->expects($this->once())
                ->method('getModelByAttribute')
                ->with(AddressMetadataManagementInterface::ENTITY_TYPE_ADDRESS, $attributeMetadata)
                ->willReturn($attribute);
            $attribute->expects($this->once())
                ->method('getUsedInForms')
                ->willReturn($attributeFormsList);
        }
        $this->addressMetadataService->expects($this->once())
            ->method('getAttributeMetadata')
            ->with($attributeCode)
            ->willReturn($attributeMetadata);

        $this->assertEquals($isAllowed, $this->model->isAttributeAllowedOnForm($attributeCode, $formName));
    }

    public function attributeOnFormDataProvider()
    {
        return [
            'metadata not exists' => [
                'isAllowed' => false,
                'isMetadataExists' => false,
                'attributeCode' => 'attribute_code',
                'formName' => 'form_name',
                'attributeFormsList' => [],
                ],
            'form not in the list' => [
                'isAllowed' => false,
                'isMetadataExists' => true,
                'attributeCode' => 'attribute_code',
                'formName' => 'form_name',
                'attributeFormsList' => ['form_1', 'form_2'],
                ],
            'allowed' => [
                'isAllowed' => true,
                'isMetadataExists' => true,
                'attributeCode' => 'attribute_code',
                'formName' => 'form_name',
                'attributeFormsList' => ['form_name', 'form_1', 'form_2'],
            ],
        ];
    }
}
