<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Swatches\Test\Unit\Model\Plugin;

use Magento\Catalog\Model\ResourceModel\Eav\Attribute;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Swatches\Helper\Data;
use Magento\Swatches\Model\Plugin\EavAttribute;
use Magento\Swatches\Model\ResourceModel\Swatch as SwatchResource;
use Magento\Swatches\Model\ResourceModel\Swatch\Collection;
use Magento\Swatches\Model\ResourceModel\Swatch\CollectionFactory;
use Magento\Swatches\Model\Swatch;
use Magento\Swatches\Model\SwatchAttributeType;
use Magento\Swatches\Model\SwatchFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Magento\Framework\Exception\InputException;

/**
 * Test plugin model for Catalog Resource Attribute
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 */
class EavAttributeTest extends TestCase
{
    private const ATTRIBUTE_ID = 123;
    private const OPTION_1_ID = 1;
    private const OPTION_2_ID = 2;
    private const ADMIN_STORE_ID = 0;
    private const DEFAULT_STORE_ID = 1;
    private const SECOND_STORE_ID = 2;
    private const NEW_OPTION_KEY = 'option_2';
    private const ATTRIBUTE_DEFAULT_VALUE = [
        0 => self::NEW_OPTION_KEY
    ];
    private const VISUAL_ATTRIBUTE_OPTIONS = [
        'value' => [
            self::OPTION_1_ID => [
                self::ADMIN_STORE_ID => 'Black',
                self::DEFAULT_STORE_ID => 'Black',
            ],
            self::NEW_OPTION_KEY => [
                self::ADMIN_STORE_ID => 'White',
                self::DEFAULT_STORE_ID => 'White',
            ],
        ]
    ];
    private const VISUAL_SWATCH_OPTIONS = [
        'value' => [
            self::OPTION_1_ID => '#000000',
            self::NEW_OPTION_KEY => '#ffffff',
        ]
    ];
    private const VISUAL_SAVED_OPTIONS = [
        [
            'value' => self::OPTION_1_ID,
            'label' => 'Black',
        ],
        [
            'value' => self::OPTION_2_ID,
            'label' => 'White',
        ]
    ];
    private const TEXT_ATTRIBUTE_OPTIONS = [
        'value' => [
            self::OPTION_1_ID => [
                self::ADMIN_STORE_ID => 'Small',
                self::DEFAULT_STORE_ID => 'Small',
            ],
            self::NEW_OPTION_KEY => [
                self::ADMIN_STORE_ID => 'Medium',
                self::DEFAULT_STORE_ID => 'Medium',
            ],
        ]
    ];
    private const TEXT_SWATCH_OPTIONS = [
        'value' => [
            self::OPTION_1_ID => [
                self::ADMIN_STORE_ID => 'S',
                self::DEFAULT_STORE_ID => 'S',
                self::SECOND_STORE_ID => '0',
            ],
            self::NEW_OPTION_KEY => [
                self::ADMIN_STORE_ID => 'M',
                self::DEFAULT_STORE_ID => 'M',
                self::SECOND_STORE_ID => '0',
            ],
        ]
    ];
    private const TEXT_SAVED_OPTIONS = [
        [
            'value' => self::OPTION_1_ID,
            'label' => 'Small',
        ],
        [
            'value' => self::OPTION_2_ID,
            'label' => 'Medium',
        ]
    ];

    /** @var EavAttribute */
    private $eavAttribute;

    /** @var Attribute|MockObject */
    private $attributeMock;

    /** @var SwatchFactory|MockObject */
    private $swatchFactoryMock;

    /** @var CollectionFactory|MockObject */
    private $collectionFactoryMock;

    /** @var Data|MockObject */
    private $swatchHelperMock;

    /** @var AbstractSource|MockObject */
    private $abstractSourceMock;

    /** @var SwatchResource|MockObject */
    private $swatchResourceMock;

    /** @var Collection|MockObject */
    private $collectionMock;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        $objectManager = new ObjectManager($this);
        $this->abstractSourceMock = $this->createMock(AbstractSource::class);
        $this->attributeMock = $this->createPartialMock(
            Attribute::class,
            ['getSource']
        );
        $this->attributeMock->setId(self::ATTRIBUTE_ID);
        $this->swatchFactoryMock = $this->createPartialMock(
            SwatchFactory::class,
            ['create']
        );
        $this->swatchHelperMock = $objectManager->getObject(
            Data::class,
            [
                'swatchTypeChecker' => $objectManager->getObject(SwatchAttributeType::class)
            ]
        );
        $this->swatchResourceMock = $this->createMock(SwatchResource::class);
        $this->collectionMock = $this->createMock(Collection::class);
        $this->collectionFactoryMock = $this->createPartialMock(CollectionFactory::class, ['create']);
        $this->attributeMock->method('getSource')
            ->willReturn($this->abstractSourceMock);
        $swatchMock = $this->createMock(Swatch::class);
        $swatchMock->method('getResource')
            ->willReturn($this->swatchResourceMock);
        $this->swatchFactoryMock->method('create')
            ->willReturn($swatchMock);

        $this->eavAttribute = new EavAttribute(
            $this->collectionFactoryMock,
            $this->swatchFactoryMock,
            $this->swatchHelperMock,
            new Json(),
            $this->swatchResourceMock
        );
    }

    /**
     * Test beforeSave plugin for visual swatch
     */
    public function testBeforeSaveVisualSwatch()
    {
        $this->attributeMock->setData(
            [
                'defaultvisual' => self::ATTRIBUTE_DEFAULT_VALUE,
                'optionvisual' => self::VISUAL_ATTRIBUTE_OPTIONS,
                'swatchvisual' => self::VISUAL_SWATCH_OPTIONS,
            ]
        );

        $this->attributeMock->setData(Swatch::SWATCH_INPUT_TYPE_KEY, Swatch::SWATCH_INPUT_TYPE_VISUAL);
        $this->eavAttribute->beforeBeforeSave($this->attributeMock);
        $this->assertEquals(self::ATTRIBUTE_DEFAULT_VALUE, $this->attributeMock->getData('default'));
        $this->assertEquals(self::VISUAL_ATTRIBUTE_OPTIONS, $this->attributeMock->getData('option'));
        $this->assertEquals(self::VISUAL_SWATCH_OPTIONS, $this->attributeMock->getData('swatch'));
    }

    /**
     * Test beforeSave plugin for text swatch
     */
    public function testBeforeSaveTextSwatch()
    {
        $this->attributeMock->setData(
            [
                'defaulttext' => self::ATTRIBUTE_DEFAULT_VALUE,
                'optiontext' => self::TEXT_ATTRIBUTE_OPTIONS,
                'swatchtext' => self::TEXT_SWATCH_OPTIONS,
            ]
        );

        $this->attributeMock->setData(Swatch::SWATCH_INPUT_TYPE_KEY, Swatch::SWATCH_INPUT_TYPE_TEXT);
        $this->eavAttribute->beforeBeforeSave($this->attributeMock);
        $this->assertEquals(self::ATTRIBUTE_DEFAULT_VALUE, $this->attributeMock->getData('default'));
        $this->assertEquals(self::TEXT_ATTRIBUTE_OPTIONS, $this->attributeMock->getData('option'));
        $this->assertEquals(self::TEXT_SWATCH_OPTIONS, $this->attributeMock->getData('swatch'));
    }

    /**
     * Test beforeSave plugin on empty label
     */
    public function testBeforeSaveWithFailedValidation()
    {
        $this->expectException(InputException::class);
        $this->expectExceptionMessage('Admin is a required field in each row');
        $options = self::VISUAL_ATTRIBUTE_OPTIONS;
        $options['value'][self::NEW_OPTION_KEY][self::ADMIN_STORE_ID] = '';
        $this->attributeMock->setData(
            [
                'defaultvisual' => self::ATTRIBUTE_DEFAULT_VALUE,
                'optionvisual' => $options,
                'swatchvisual' => self::VISUAL_SWATCH_OPTIONS,
            ]
        );

        $this->attributeMock->setData(Swatch::SWATCH_INPUT_TYPE_KEY, Swatch::SWATCH_INPUT_TYPE_VISUAL);
        $this->eavAttribute->beforeBeforeSave($this->attributeMock);
    }

    /**
     * Test beforeSave plugin on empty label of option being deleted
     */
    public function testValidationIsSkippedForDeletedOption()
    {
        $options = self::VISUAL_ATTRIBUTE_OPTIONS;
        $options['value'][self::NEW_OPTION_KEY][self::ADMIN_STORE_ID] = '';
        $options['delete'][self::NEW_OPTION_KEY] = '1';
        $this->attributeMock->setData(
            [
                'defaultvisual' => self::ATTRIBUTE_DEFAULT_VALUE,
                'optionvisual' => $options,
                'swatchvisual' => self::VISUAL_SWATCH_OPTIONS,
            ]
        );

        $this->attributeMock->setData(Swatch::SWATCH_INPUT_TYPE_KEY, Swatch::SWATCH_INPUT_TYPE_VISUAL);
        $this->eavAttribute->beforeBeforeSave($this->attributeMock);
        $this->assertEquals(self::ATTRIBUTE_DEFAULT_VALUE, $this->attributeMock->getData('default'));
        $this->assertEquals($options, $this->attributeMock->getData('option'));
        $this->assertEquals(self::VISUAL_SWATCH_OPTIONS, $this->attributeMock->getData('swatch'));
    }

    /**
     * Test beforeSave plugin for non a swatch attribute
     */
    public function testBeforeSaveNotSwatch()
    {
        $additionalData = [
            Swatch::SWATCH_INPUT_TYPE_KEY => Swatch::SWATCH_INPUT_TYPE_VISUAL,
            'update_product_preview_image' => 1,
            'use_product_image_for_swatch' => 0
        ];

        $this->attributeMock->setData(
            [
                Swatch::SWATCH_INPUT_TYPE_KEY => Swatch::SWATCH_INPUT_TYPE_DROPDOWN,
                'additional_data' => json_encode($additionalData),
            ]
        );

        $this->attributeMock->setData(Swatch::SWATCH_INPUT_TYPE_KEY, Swatch::SWATCH_INPUT_TYPE_DROPDOWN);

        $this->eavAttribute->beforeBeforeSave($this->attributeMock);

        unset($additionalData[Swatch::SWATCH_INPUT_TYPE_KEY]);

        $this->assertEquals(json_encode($additionalData), $this->attributeMock->getData('additional_data'));
    }

    /**
     * @return array
     */
    public function visualSwatchProvider()
    {
        return [
            [Swatch::SWATCH_TYPE_EMPTY, 'black', 'white'],
            [Swatch::SWATCH_TYPE_VISUAL_COLOR, '#000000', '#ffffff'],
            [Swatch::SWATCH_TYPE_VISUAL_IMAGE, '/path/black.png', '/path/white.png'],
        ];
    }

    /**
     * Test afterSave plugin for visual swatch
     *
     * @param int $swatchType
     * @param string $swatch1
     * @param string $swatch2
     *
     * @dataProvider visualSwatchProvider
     */
    public function testAfterAfterSaveVisualSwatch(int $swatchType, string $swatch1, string $swatch2)
    {
        $options = self::VISUAL_SWATCH_OPTIONS;
        $options['value'][self::OPTION_1_ID] = $swatch1;
        $options['value'][self::NEW_OPTION_KEY] = $swatch2;
        $this->attributeMock->addData(
            [
                'defaultvisual' => self::ATTRIBUTE_DEFAULT_VALUE,
                'optionvisual' => self::VISUAL_ATTRIBUTE_OPTIONS,
                'swatchvisual' => $options,
            ]
        );

        $this->attributeMock->setData(Swatch::SWATCH_INPUT_TYPE_KEY, Swatch::SWATCH_INPUT_TYPE_VISUAL);
        $this->eavAttribute->beforeBeforeSave($this->attributeMock);
        $this->abstractSourceMock->expects($this->once())
            ->method('getAllOptions')
            ->willReturn(self::VISUAL_SAVED_OPTIONS);

        $this->swatchResourceMock->expects($this->once())
            ->method('saveDefaultSwatchOption')
            ->with(self::ATTRIBUTE_ID, self::OPTION_2_ID);

        $this->collectionMock->expects($this->exactly(4))
            ->method('addFieldToFilter')
            ->withConsecutive(
                ['option_id', self::OPTION_1_ID],
                ['store_id', self::ADMIN_STORE_ID],
                ['option_id', self::OPTION_2_ID],
                ['store_id', self::ADMIN_STORE_ID]
            )
            ->willReturnSelf();

        $this->collectionMock->expects($this->exactly(2))
            ->method('getFirstItem')
            ->willReturnOnConsecutiveCalls(
                $this->createSwatchMock(
                    (string)$swatchType,
                    $swatch1 ?: null,
                    1
                ),
                $this->createSwatchMock(
                    (string)$swatchType,
                    $swatch2 ?: null,
                    null,
                    self::OPTION_2_ID,
                    self::ADMIN_STORE_ID
                )
            );
        $this->collectionFactoryMock->expects($this->exactly(2))
            ->method('create')
            ->willReturn($this->collectionMock);

        $this->eavAttribute->afterAfterSave($this->attributeMock);
    }

    /**
     * Test afterSave plugin for text swatch
     */
    public function testAfterAfterSaveTextualSwatch()
    {
        $this->attributeMock->addData(
            [
                'defaulttext' => self::ATTRIBUTE_DEFAULT_VALUE,
                'optiontext' => self::TEXT_ATTRIBUTE_OPTIONS,
                'swatchtext' => self::TEXT_SWATCH_OPTIONS,
            ]
        );

        $this->attributeMock->setData(Swatch::SWATCH_INPUT_TYPE_KEY, Swatch::SWATCH_INPUT_TYPE_TEXT);
        $this->eavAttribute->beforeBeforeSave($this->attributeMock);

        $this->abstractSourceMock->expects($this->once())
            ->method('getAllOptions')
            ->willReturn(self::TEXT_SAVED_OPTIONS);

        $this->swatchResourceMock->expects($this->once())
            ->method('saveDefaultSwatchOption')
            ->with(self::ATTRIBUTE_ID, self::OPTION_2_ID);

        $this->collectionMock->expects($this->exactly(12))
            ->method('addFieldToFilter')
            ->withConsecutive(
                ['option_id', self::OPTION_1_ID],
                ['store_id', self::ADMIN_STORE_ID],
                ['option_id', self::OPTION_1_ID],
                ['store_id', self::DEFAULT_STORE_ID],
                ['option_id', self::OPTION_1_ID],
                ['store_id', self::SECOND_STORE_ID],
                ['option_id', self::OPTION_2_ID],
                ['store_id', self::ADMIN_STORE_ID],
                ['option_id', self::OPTION_2_ID],
                ['store_id', self::DEFAULT_STORE_ID],
                ['option_id', self::OPTION_2_ID],
                ['store_id', self::SECOND_STORE_ID]
            )
            ->willReturnSelf();

        $this->collectionMock->expects($this->exactly(6))
            ->method('getFirstItem')
            ->willReturnOnConsecutiveCalls(
                $this->createSwatchMock(
                    (string)Swatch::SWATCH_TYPE_TEXTUAL,
                    self::TEXT_SWATCH_OPTIONS['value'][self::OPTION_1_ID][self::ADMIN_STORE_ID],
                    1
                ),
                $this->createSwatchMock(
                    (string)Swatch::SWATCH_TYPE_TEXTUAL,
                    self::TEXT_SWATCH_OPTIONS['value'][self::OPTION_1_ID][self::DEFAULT_STORE_ID],
                    1
                ),
                $this->createSwatchMock(
                    (string)Swatch::SWATCH_TYPE_TEXTUAL,
                    self::TEXT_SWATCH_OPTIONS['value'][self::OPTION_1_ID][self::SECOND_STORE_ID],
                    1
                ),
                $this->createSwatchMock(
                    (string)Swatch::SWATCH_TYPE_TEXTUAL,
                    self::TEXT_SWATCH_OPTIONS['value'][self::NEW_OPTION_KEY][self::ADMIN_STORE_ID],
                    null,
                    self::OPTION_2_ID,
                    self::ADMIN_STORE_ID
                ),
                $this->createSwatchMock(
                    (string)Swatch::SWATCH_TYPE_TEXTUAL,
                    self::TEXT_SWATCH_OPTIONS['value'][self::NEW_OPTION_KEY][self::DEFAULT_STORE_ID],
                    null,
                    self::OPTION_2_ID,
                    self::DEFAULT_STORE_ID
                ),
                $this->createSwatchMock(
                    (string)Swatch::SWATCH_TYPE_TEXTUAL,
                    self::TEXT_SWATCH_OPTIONS['value'][self::NEW_OPTION_KEY][self::SECOND_STORE_ID],
                    null,
                    self::OPTION_2_ID,
                    self::SECOND_STORE_ID
                )
            );
        $this->collectionFactoryMock->expects($this->exactly(6))
            ->method('create')
            ->willReturn($this->collectionMock);

        $this->eavAttribute->afterAfterSave($this->attributeMock);
    }

    /**
     * Test afterSave plugin for deleted visual swatch option
     */
    public function testAfterAfterSaveVisualSwatchIsDelete()
    {
        $options = self::VISUAL_ATTRIBUTE_OPTIONS;
        $options['delete'][self::OPTION_1_ID] = '1';
        $this->attributeMock->addData(
            [
                'defaultvisual' => self::ATTRIBUTE_DEFAULT_VALUE,
                'optionvisual' => $options,
                'swatchvisual' => self::VISUAL_SWATCH_OPTIONS,
            ]
        );

        $this->attributeMock->setData(Swatch::SWATCH_INPUT_TYPE_KEY, Swatch::SWATCH_INPUT_TYPE_VISUAL);
        $this->eavAttribute->beforeBeforeSave($this->attributeMock);
        $this->abstractSourceMock->expects($this->once())
            ->method('getAllOptions')
            ->willReturn(self::VISUAL_SAVED_OPTIONS);

        $this->swatchResourceMock->expects($this->once())
            ->method('saveDefaultSwatchOption')
            ->with(self::ATTRIBUTE_ID, self::OPTION_2_ID);

        $this->collectionMock->expects($this->exactly(2))
            ->method('addFieldToFilter')
            ->withConsecutive(
                ['option_id', self::OPTION_2_ID],
                ['store_id', self::ADMIN_STORE_ID]
            )
            ->willReturnSelf();

        $this->collectionMock->expects($this->exactly(1))
            ->method('getFirstItem')
            ->willReturnOnConsecutiveCalls(
                $this->createSwatchMock(
                    (string)Swatch::SWATCH_TYPE_VISUAL_COLOR,
                    self::VISUAL_SWATCH_OPTIONS['value'][self::NEW_OPTION_KEY],
                    null,
                    self::OPTION_2_ID,
                    self::ADMIN_STORE_ID
                )
            );
        $this->collectionFactoryMock->expects($this->exactly(1))
            ->method('create')
            ->willReturn($this->collectionMock);

        $this->eavAttribute->afterAfterSave($this->attributeMock);
    }

    /**
     * Test afterSave plugin for deleted text swatch option
     */
    public function testAfterAfterSaveTextualSwatchIsDelete()
    {
        $options = self::TEXT_ATTRIBUTE_OPTIONS;
        $options['delete'][self::OPTION_1_ID] = '1';
        $this->attributeMock->addData(
            [
                'defaulttext' => self::ATTRIBUTE_DEFAULT_VALUE,
                'optiontext' => $options,
                'swatchtext' => self::TEXT_SWATCH_OPTIONS,
            ]
        );

        $this->attributeMock->setData(Swatch::SWATCH_INPUT_TYPE_KEY, Swatch::SWATCH_INPUT_TYPE_TEXT);
        $this->eavAttribute->beforeBeforeSave($this->attributeMock);

        $this->abstractSourceMock->expects($this->once())
            ->method('getAllOptions')
            ->willReturn(self::TEXT_SAVED_OPTIONS);

        $this->swatchResourceMock->expects($this->once())
            ->method('saveDefaultSwatchOption')
            ->with(self::ATTRIBUTE_ID, self::OPTION_2_ID);

        $this->collectionMock->expects($this->exactly(6))
            ->method('addFieldToFilter')
            ->withConsecutive(
                ['option_id', self::OPTION_2_ID],
                ['store_id', self::ADMIN_STORE_ID],
                ['option_id', self::OPTION_2_ID],
                ['store_id', self::DEFAULT_STORE_ID],
                ['option_id', self::OPTION_2_ID],
                ['store_id', self::SECOND_STORE_ID]
            )
            ->willReturnSelf();

        $this->collectionMock->expects($this->exactly(3))
            ->method('getFirstItem')
            ->willReturnOnConsecutiveCalls(
                $this->createSwatchMock(
                    (string)Swatch::SWATCH_TYPE_TEXTUAL,
                    self::TEXT_SWATCH_OPTIONS['value'][self::NEW_OPTION_KEY][self::ADMIN_STORE_ID],
                    null,
                    self::OPTION_2_ID,
                    self::ADMIN_STORE_ID
                ),
                $this->createSwatchMock(
                    (string)Swatch::SWATCH_TYPE_TEXTUAL,
                    self::TEXT_SWATCH_OPTIONS['value'][self::NEW_OPTION_KEY][self::DEFAULT_STORE_ID],
                    null,
                    self::OPTION_2_ID,
                    self::DEFAULT_STORE_ID
                ),
                $this->createSwatchMock(
                    (string)Swatch::SWATCH_TYPE_TEXTUAL,
                    self::TEXT_SWATCH_OPTIONS['value'][self::NEW_OPTION_KEY][self::SECOND_STORE_ID],
                    null,
                    self::OPTION_2_ID,
                    self::SECOND_STORE_ID
                )
            );
        $this->collectionFactoryMock->expects($this->exactly(3))
            ->method('create')
            ->willReturn($this->collectionMock);

        $this->eavAttribute->afterAfterSave($this->attributeMock);
    }

    /**
     * Test afterSave plugin on empty swatch value
     */
    public function testAfterAfterSaveNotSwatchAttribute()
    {
        $options = self::TEXT_SWATCH_OPTIONS;
        $options['value'][self::OPTION_1_ID][self::ADMIN_STORE_ID] = null;
        $options['value'][self::OPTION_1_ID][self::DEFAULT_STORE_ID] = null;
        $options['value'][self::OPTION_1_ID][self::SECOND_STORE_ID] = null;
        $options['value'][self::NEW_OPTION_KEY][self::ADMIN_STORE_ID] = null;
        $options['value'][self::NEW_OPTION_KEY][self::DEFAULT_STORE_ID] = null;
        $options['value'][self::NEW_OPTION_KEY][self::SECOND_STORE_ID] = null;
        $this->attributeMock->addData(
            [
                'defaulttext' => self::ATTRIBUTE_DEFAULT_VALUE,
                'optiontext' => self::TEXT_ATTRIBUTE_OPTIONS,
                'swatchtext' => $options,
            ]
        );

        $this->attributeMock->setData(Swatch::SWATCH_INPUT_TYPE_KEY, Swatch::SWATCH_INPUT_TYPE_TEXT);
        $this->eavAttribute->beforeBeforeSave($this->attributeMock);

        $this->abstractSourceMock->expects($this->once())
            ->method('getAllOptions')
            ->willReturn(self::TEXT_SAVED_OPTIONS);

        $this->swatchResourceMock->expects($this->once())
            ->method('saveDefaultSwatchOption')
            ->with(self::ATTRIBUTE_ID, self::OPTION_2_ID);

        $this->collectionMock->expects($this->exactly(12))
            ->method('addFieldToFilter')
            ->withConsecutive(
                ['option_id', self::OPTION_1_ID],
                ['store_id', self::ADMIN_STORE_ID],
                ['option_id', self::OPTION_1_ID],
                ['store_id', self::DEFAULT_STORE_ID],
                ['option_id', self::OPTION_1_ID],
                ['store_id', self::SECOND_STORE_ID],
                ['option_id', self::OPTION_2_ID],
                ['store_id', self::ADMIN_STORE_ID],
                ['option_id', self::OPTION_2_ID],
                ['store_id', self::DEFAULT_STORE_ID],
                ['option_id', self::OPTION_2_ID],
                ['store_id', self::SECOND_STORE_ID]
            )
            ->willReturnSelf();

        $this->collectionMock->expects($this->exactly(6))
            ->method('getFirstItem')
            ->willReturnOnConsecutiveCalls(
                $this->createSwatchMock(
                    (string)Swatch::SWATCH_TYPE_TEXTUAL,
                    null,
                    1
                ),
                $this->createSwatchMock(
                    (string)Swatch::SWATCH_TYPE_TEXTUAL,
                    null,
                    1
                ),
                $this->createSwatchMock(
                    (string)Swatch::SWATCH_TYPE_TEXTUAL,
                    null,
                    1
                ),
                $this->createSwatchMock(
                    (string)Swatch::SWATCH_TYPE_TEXTUAL,
                    null,
                    null,
                    self::OPTION_2_ID,
                    self::ADMIN_STORE_ID
                ),
                $this->createSwatchMock(
                    (string)Swatch::SWATCH_TYPE_TEXTUAL,
                    null,
                    null,
                    self::OPTION_2_ID,
                    self::DEFAULT_STORE_ID
                ),
                $this->createSwatchMock(
                    (string)Swatch::SWATCH_TYPE_TEXTUAL,
                    null,
                    null,
                    self::OPTION_2_ID,
                    self::SECOND_STORE_ID
                )
            );
        $this->collectionFactoryMock->expects($this->exactly(6))
            ->method('create')
            ->willReturn($this->collectionMock);

        $this->eavAttribute->afterAfterSave($this->attributeMock);
    }

    /**
     * Create configured mock for swatch model
     *
     * @param string $type
     * @param string|null $value
     * @param int|null $id
     * @param int|null $optionId
     * @param int|null $storeId
     * @return MockObject
     */
    private function createSwatchMock(
        string $type,
        ?string $value,
        ?int $id = null,
        ?int $optionId = null,
        ?int $storeId = null
    ) {
        $swatch = $this->createMock(Swatch::class);
        $swatch->method('getId')
            ->willReturn($id);
        $swatch->method('getResource')
            ->willReturn($this->swatchResourceMock);
        $swatch->expects($this->once())
            ->method('save');
        if ($id) {
            $swatch->expects($this->exactly(2))
                ->method('setData')
                ->withConsecutive(
                    ['type', $type],
                    ['value', $value]
                );
        } else {
            $swatch->expects($this->exactly(4))
                ->method('setData')
                ->withConsecutive(
                    ['option_id', $optionId],
                    ['store_id', $storeId],
                    ['type', $type],
                    ['value', $value]
                );
        }
        return $swatch;
    }
}
