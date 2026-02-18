<?php
/**
 * Copyright 2026 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Block\Adminhtml\Helper\Form\Wysiwyg;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Form\Element\ElementCreator;
use Magento\Catalog\Block\Adminhtml\Helper\Form\Wysiwyg\Content;
use Magento\Cms\Model\Wysiwyg\Config as WysiwygConfig;
use Magento\Directory\Helper\Data as DirectoryHelper;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * @covers \Magento\Catalog\Block\Adminhtml\Helper\Form\Wysiwyg\Content
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ContentTest extends TestCase
{
    /**
     * Admin WYSIWYG save action URL used across tests.
     *
     * @var string
     */
    private const ACTION_URL = '/admin/catalog/wysiwyg/save';

    /**
     * Base media URL used across tests.
     *
     * @var string
     */
    private const STORE_MEDIA_URL = 'https://example.com/media/';

    /**
     * @var Content
     */
    private Content $block;

    /**
     * @var Context|MockObject
     */
    private $contextMock;

    /**
     * @var Registry|MockObject
     */
    private $registryMock;

    /**
     * @var FormFactory|MockObject
     */
    private $formFactoryMock;

    /**
     * @var WysiwygConfig|MockObject
     */
    private $wysiwygConfigMock;

    /**
     * @var Form|MockObject
     */
    private $formMock;

    /**
     * @var JsonHelper|MockObject
     */
    private $jsonHelperMock;

    /**
     * @var DirectoryHelper|MockObject
     */
    private $directoryHelperMock;

    /**
     * @var ElementCreator|MockObject
     */
    private $elementCreatorMock;

    /**
     * @var UrlInterface|MockObject
     */
    private $urlBuilderMock;

    /**
     * @return void
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    protected function setUp(): void
    {
        $this->contextMock         = $this->createMock(Context::class);
        $this->registryMock        = $this->createMock(Registry::class);
        $this->formFactoryMock     = $this->createMock(FormFactory::class);
        $this->wysiwygConfigMock   = $this->createMock(WysiwygConfig::class);
        $this->formMock            = $this->createMock(Form::class);
        $this->jsonHelperMock      = $this->createMock(JsonHelper::class);
        $this->directoryHelperMock = $this->createMock(DirectoryHelper::class);
        $this->elementCreatorMock  = $this->createMock(ElementCreator::class);
        $this->urlBuilderMock      = $this->createMock(UrlInterface::class);

        $this->urlBuilderMock->method('getBaseUrl')
            ->willReturn('http://localhost/');
        $this->contextMock->method('getUrlBuilder')
            ->willReturn($this->urlBuilderMock);

        $objectManager = new ObjectManager($this);
        $objectManager->prepareObjectManager([
            [JsonHelper::class, $this->jsonHelperMock],
            [DirectoryHelper::class, $this->directoryHelperMock],
            [ElementCreator::class, $this->elementCreatorMock],
        ]);

        $this->block = $objectManager->getObject(
            Content::class,
            [
                'context'       => $this->contextMock,
                'registry'      => $this->registryMock,
                'formFactory'   => $this->formFactoryMock,
                'wysiwygConfig' => $this->wysiwygConfigMock,
            ]
        );
    }

    /**
     * Verify that the constructor stores the Wysiwyg config dependency.
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Helper\Form\Wysiwyg\Content::__construct
     * @return void
     */
    public function testConstructorStoresWysiwygConfig(): void
    {
        $actual = $this->accessMember($this->block, '_wysiwygConfig', isMethod: false);
        $this->assertSame($this->wysiwygConfigMock, $actual);
    }

    /**
     * Ensure _prepareForm creates a form and assigns it to the block.
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Helper\Form\Wysiwyg\Content::_prepareForm
     * @return void
     */
    public function testPrepareFormSetsFormOnBlock(): void
    {
        $storeId         = 3;
        $editorElementId = 'content_editor';

        $this->block->setData('action', self::ACTION_URL);
        $this->block->setData('store_media_url', self::STORE_MEDIA_URL);
        $this->block->setData('store_id', $storeId);
        $this->block->setData('editor_element_id', $editorElementId);

        $expectedCreateArgs = [
            'data' => [
                'id'     => 'wysiwyg_edit_form',
                'action' => self::ACTION_URL,
                'method' => 'post',
            ],
        ];

        $this->formFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with($expectedCreateArgs)
            ->willReturn($this->formMock);

        $this->wysiwygConfigMock
            ->expects($this->once())
            ->method('getConfig')
            ->willReturn(new DataObject(['sample' => 'value']));

        $this->formMock
            ->expects($this->once())
            ->method('addField')
            ->willReturnCallback(function ($id, $type, array $fieldConfig) use ($editorElementId) {
                self::assertSame($editorElementId, $id);
                self::assertSame('editor', $type);
                self::assertIsArray($fieldConfig);
                return $this->formMock;
            });

        $this->accessMember($this->block, '_prepareForm');

        $this->assertSame($this->formMock, $this->block->getForm());
    }

    /**
     * Verify that the expected configuration array is passed to the Wysiwyg config.
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Helper\Form\Wysiwyg\Content::_prepareForm
     * @return void
     */
    public function testPrepareFormPassesExpectedConfigToWysiwyg(): void
    {
        $storeId         = 5;
        $editorElementId = 'content_editor';

        $this->block->setData('action', self::ACTION_URL);
        $this->block->setData('store_media_url', self::STORE_MEDIA_URL);
        $this->block->setData('store_id', $storeId);
        $this->block->setData('editor_element_id', $editorElementId);

        $this->formFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with($this->isType('array'))
            ->willReturn($this->formMock);

        $this->wysiwygConfigMock
            ->expects($this->once())
            ->method('getConfig')
            ->willReturnCallback(function (array $config) {
                self::assertTrue($this->isValidWysiwygConfig($config));
                return new DataObject(['sample' => 'value']);
            });

        $this->formMock
            ->expects($this->once())
            ->method('addField')
            ->willReturnCallback(function ($id, $type, array $fieldConfig) use ($editorElementId) {
                self::assertSame($editorElementId, $id);
                self::assertSame('editor', $type);
                self::assertIsArray($fieldConfig);
                return $this->formMock;
            });

        $result = $this->accessMember($this->block, '_prepareForm');

        $this->assertSame($this->block, $result);
    }

    /**
     * Ensure the editor field is added with the exact parameters Magento expects.
     *
     * @covers \Magento\Catalog\Block\Adminhtml\Helper\Form\Wysiwyg\Content::_prepareForm
     * @return void
     */
    public function testPrepareFormAddsEditorFieldWithExpectedParams(): void
    {
        $storeId         = 7;
        $editorElementId = 'content_editor';

        $this->block->setData('action', self::ACTION_URL);
        $this->block->setData('store_media_url', self::STORE_MEDIA_URL);
        $this->block->setData('store_id', $storeId);
        $this->block->setData('editor_element_id', $editorElementId);

        $this->formFactoryMock
            ->expects($this->once())
            ->method('create')
            ->with($this->isType('array'))
            ->willReturn($this->formMock);

        $returnedEditorConfig = new DataObject(['sample' => 'value']);

        $this->wysiwygConfigMock
            ->expects($this->once())
            ->method('getConfig')
            ->with($this->isType('array'))
            ->willReturn($returnedEditorConfig);

        $this->formMock
            ->expects($this->once())
            ->method('addField')
            ->willReturnCallback(
                function (
                    $id,
                    $type,
                    array $fieldConfig
                ) use (
                    $editorElementId,
                    $returnedEditorConfig
                ) {
                    self::assertSame($editorElementId, $id);
                    self::assertSame('editor', $type);
                    self::assertArrayHasKey('name', $fieldConfig);
                    self::assertArrayHasKey('style', $fieldConfig);
                    self::assertArrayHasKey('required', $fieldConfig);
                    self::assertArrayHasKey('force_load', $fieldConfig);
                    self::assertArrayHasKey('config', $fieldConfig);
                    self::assertSame('content', $fieldConfig['name']);
                    self::assertSame('width:725px;height:460px', $fieldConfig['style']);
                    self::assertTrue($fieldConfig['required']);
                    self::assertTrue($fieldConfig['force_load']);
                    self::assertSame($returnedEditorConfig, $fieldConfig['config']);
                    return $this->formMock;
                }
            );

        $result = $this->accessMember($this->block, '_prepareForm');

        $this->assertSame($this->block, $result);
    }

    /**
     * Callback used to validate the Wysiwyg config array.
     *
     * @param array $config
     * @return bool
     */
    private function isValidWysiwygConfig(array $config): bool
    {
        return isset(
            $config['document_base_url'],
            $config['store_id'],
            $config['add_variables'],
            $config['add_widgets'],
            $config['add_directives'],
            $config['use_container'],
            $config['container_class']
        )
            && $config['add_variables'] === false
            && $config['add_widgets'] === false
            && $config['add_directives'] === true
            && $config['use_container'] === true
            && $config['container_class'] === 'hor-scroll';
    }

    /**
     * Unified helper that can either read a non‑public property **or**
     * invoke a non‑public method on a given object.
     *
     * @param object $object
     * @param string $member
     * @param array  $args
     * @param bool   $isMethod
     *
     * @return mixed
     */
    private function accessMember(
        object $object,
        string $member,
        array $args = [],
        bool $isMethod = true
    ): mixed {
        $ref = new ReflectionClass($object);

        if ($isMethod) {
            $method = $ref->getMethod($member);
            $method->setAccessible(true);
            return $method->invokeArgs($object, $args);
        }

        $property = $ref->getProperty($member);
        $property->setAccessible(true);
        return $property->getValue($object);
    }
}
