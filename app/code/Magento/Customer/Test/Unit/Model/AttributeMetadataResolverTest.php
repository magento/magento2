<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Customer\Test\Unit\Model;

use Magento\Config\Model\Config\Source\Nooptreq;
use Magento\Customer\Helper\Address as AddressHelper;
use Magento\Customer\Model\Attribute;
use Magento\Customer\Model\AttributeMetadataResolver;
use Magento\Customer\Model\FileUploaderDataResolver;
use Magento\Customer\Model\GroupManagement;
use Magento\Customer\Model\Options as CustomerOptions;
use Magento\Eav\Model\Entity\Type;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Ui\DataProvider\EavValidationRules;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

/**
 * Class AttributeMetadataResolverTest
 *
 * Validate attributeMetadata contains correct values in meta data array
 */
class AttributeMetadataResolverTest extends TestCase
{
    /**
     * @var AttributeMetadataResolver
     */
    private $model;

    /**
     * @var EavValidationRules|MockObject
     */
    private $eavValidationRulesMock;

    /**
     * @var FileUploaderDataResolver|MockObject
     */
    private $fileUploaderDataResolverMock;

    /**
     * @var GroupManagement|MockObject
     */
    private $groupManagementMock;

    /**
     * @var CustomerOptions|MockObject
     */
    private $customerOptionsMock;

    /**
     * @var AddressHelper|MockObject
     */
    private $addressHelperMock;

    /**
     * @inheritdoc
     */
    public function setUp()
    {
        $this->eavValidationRulesMock = $this->getMockBuilder(EavValidationRules::class)
            ->setMethods(['build'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->fileUploaderDataResolverMock = $this->getMockBuilder(FileUploaderDataResolver::class)
            ->setMethods(['overrideFileUploaderMetadata'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->groupManagementMock = $this->getMockBuilder(GroupManagement::class)
            ->setMethods(['getId', 'getDefaultGroup'])
            ->disableOriginalConstructor()
            ->getMock();
        $this->customerOptionsMock = $this->getMockBuilder(CustomerOptions::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->addressHelperMock = $this->getMockBuilder(AddressHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManager = new ObjectManager($this);
        $this->model = $objectManager->getObject(
            AttributeMetadataResolver::class,
            [
                'eavValidationRules' => $this->eavValidationRulesMock,
                'fileUploaderDataResolver' => $this->fileUploaderDataResolverMock,
                'groupManagement' => $this->groupManagementMock,
                'customerOptions' => $this->customerOptionsMock,
                'addressHelper' => $this->addressHelperMock
            ]
        );
    }

    /**
     * Test to get meta data of the customer or customer address attribute
     *
     * @return void
     */
    public function testGetAttributesMetaHasDefaultAttributeValue(): void
    {
        $attributeMock = $this->getMockBuilder(Attribute::class)
            ->setMethods([
                'usesSource',
                'getDataUsingMethod',
                'getAttributeCode',
                'getFrontendInput',
                'getSource',
                'setDataUsingMethod'
            ])
            ->disableOriginalConstructor()
            ->getMock();

        $rules = [
            'required-entry' => true
        ];
        $defaultGroupId = '3';
        $allowToShowHiddenAttributes = false;
        $usesSource = false;
        $entityType = $this->getMockBuilder(Type::class)
            ->disableOriginalConstructor()
            ->getMock();
        $attributeMock->expects($this->once())
            ->method('usesSource')
            ->willReturn($usesSource);
        $attributeMock->expects($this->exactly(2))
            ->method('getAttributeCode')
            ->willReturn('group_id');
        $this->groupManagementMock->expects($this->once())
            ->method('getDefaultGroup')
            ->willReturnSelf();
        $this->groupManagementMock->expects($this->once())
            ->method('getId')
            ->willReturn($defaultGroupId);
        $attributeMock->expects($this->at(9))
            ->method('getDataUsingMethod')
            ->with('default_value')
            ->willReturn($defaultGroupId);
        $attributeMock->expects($this->once())
            ->method('setDataUsingMethod')
            ->willReturnSelf();
        $this->eavValidationRulesMock->expects($this->once())
            ->method('build')
            ->with($attributeMock)
            ->willReturn($rules);
        $this->fileUploaderDataResolverMock->expects($this->once())
            ->method('overrideFileUploaderMetadata')
            ->with($entityType, $attributeMock)
            ->willReturnSelf();

        $meta = $this->model->getAttributesMeta($attributeMock, $entityType, $allowToShowHiddenAttributes);
        $this->assertArrayHasKey('default', $meta['arguments']['data']['config']);
        $this->assertEquals($defaultGroupId, $meta['arguments']['data']['config']['default']);
    }

    /**
     * @dataProvider getAttributesMetaPrefixSuffixProvider
     * @param $attributeCode
     * @param $isRequired
     * @param $options
     * @param $result
     * @throws LocalizedException
     */
    public function testGetAttributesMetaPrefixSuffix($attributeCode, $isRequired, $options, $result)
    {
        $attributeMock = $this->getAttribute($attributeCode);
        $typeMock = $this->getMockBuilder(Type::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->addressHelperMock->expects($this->once())
            ->method('getConfig')
            ->with($attributeCode . '_show')
            ->willReturn($isRequired);

        if ($attributeCode === 'prefix') {
            $this->customerOptionsMock->expects($this->once())
                ->method('getNamePrefixOptions')
                ->willReturn($options);
        } elseif ($attributeCode === 'suffix') {
            $this->customerOptionsMock->expects($this->once())
                ->method('getNameSuffixOptions')
                ->willReturn($options);
        }

        $meta = $this->model->getAttributesMeta(
            $attributeMock,
            $typeMock,
            true
        );
        unset(
            $meta['arguments']['data']['config']['visible'],
            $meta['arguments']['data']['config']['label'],
            $meta['arguments']['data']['config']['sortOrder'],
            $meta['arguments']['data']['config']['default'],
            $meta['arguments']['data']['config']['notice'],
            $meta['arguments']['data']['config']['size'],
            $meta['arguments']['data']['config']['componentType'],
            $meta['arguments']['data']['config']['__disableTmpl']
        );
        $this->assertEquals(
            $result,
            $meta['arguments']['data']['config']
        );
    }

    /**
     * @return array
     */
    public function getAttributesMetaPrefixSuffixProvider()
    {
        return [
            [
                'prefix',
                Nooptreq::VALUE_REQUIRED,
                false,
                [
                    'dataType' => 'text',
                    'formElement' => 'input',
                    'required' => 1
                ]
            ],
            [
                'prefix',
                Nooptreq::VALUE_OPTIONAL,
                [
                    ' ' => ' ',
                    'mr' => 'mr',
                    'mrs' => 'mrs'
                ],
                [
                    'dataType' => 'select',
                    'formElement' => 'select',
                    'required' => 0,
                    'options' => [
                        [
                            'label' => ' ',
                            'value' => ''
                        ],
                        [
                            'label' => 'mr',
                            'value' => 'mr'
                        ],
                        [
                            'label' => 'mrs',
                            'value' => 'mrs'
                        ]
                    ]
                ]
            ],
            [
                'suffix',
                Nooptreq::VALUE_OPTIONAL,
                false,
                [
                    'dataType' => 'text',
                    'formElement' => 'input',
                    'required' => 0
                ]
            ],
            [
                'suffix',
                Nooptreq::VALUE_REQUIRED,
                [
                    ' ' => ' ',
                    'jr' => 'jr',
                    'sr' => 'sr'
                ],
                [
                    'dataType' => 'select',
                    'formElement' => 'select',
                    'required' => 1,
                    'options' => [
                        [
                            'label' => ' ',
                            'value' => ''
                        ],
                        [
                            'label' => 'jr',
                            'value' => 'jr'
                        ],
                        [
                            'label' => 'sr',
                            'value' => 'sr'
                        ]
                    ]
                ]
            ],
        ];
    }

    /**
     * @param string $attributeCode
     * @return Attribute|MockObject
     */
    private function getAttribute($attributeCode)
    {
        $attribute = $this->getMockBuilder(Attribute::class)
            ->disableOriginalConstructor()
            ->setMethods(['usesSource', 'getDataUsingMethod', 'getAttributeCode'])
            ->getMock();
        $attribute->method('usesSource')
            ->willReturn(false);
        $attribute->method('getDataUsingMethod')
            ->willReturnMap(
                [
                    ['frontend_input', null, 'text'],
                    ['is_required', null, 1]
                ]
            );
        $attribute->method('getAttributeCode')
            ->willReturn($attributeCode);

        return $attribute;
    }
}
