<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Catalog\Test\Unit\Block\Adminhtml\Product\Edit;

use Magento\Backend\Block\Template\Context;
use Magento\Catalog\Block\Adminhtml\Product\Edit\NewCategory;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\ResourceModel\Category\Collection;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\Form\Element\Fieldset;
use Magento\Framework\Data\Form\Element\Note;
use Magento\Framework\Data\Form\Element\Select;
use Magento\Framework\Data\Form\Element\Text;
use Magento\Framework\Data\FormFactory;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Phrase;
use Magento\Framework\Registry;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Helper\SecureHtmlRenderer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Unit test for NewCategory block.
 *
 * @covers \Magento\Catalog\Block\Adminhtml\Product\Edit\NewCategory
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class NewCategoryTest extends TestCase
{
    /**
     * @var NewCategory
     */
    private NewCategory $block;

    /**
     * @var FormFactory|MockObject
     */
    private FormFactory|MockObject $formFactory;

    /**
     * @var Registry|MockObject
     */
    private Registry|MockObject $registry;

    /**
     * @var EncoderInterface|MockObject
     */
    private EncoderInterface|MockObject $jsonEncoder;

    /**
     * @var CategoryFactory|MockObject
     */
    private CategoryFactory|MockObject $categoryFactory;

    /**
     * @var SecureHtmlRenderer|MockObject
     */
    private SecureHtmlRenderer|MockObject $secureRenderer;

    /**
     * @var UrlInterface|MockObject
     */
    private UrlInterface|MockObject $urlBuilder;

    /**
     * Set up test dependencies and mocks.
     *
     * @return void
     */
    protected function setUp(): void
    {
        $objectManagerHelper = new ObjectManager($this);
        $objectManagerHelper->prepareObjectManager();

        $this->formFactory = $this->createMock(FormFactory::class);
        $this->registry = $this->createMock(Registry::class);
        $this->jsonEncoder = $this->getMockForAbstractClass(EncoderInterface::class);
        $this->categoryFactory = $this->createMock(CategoryFactory::class);
        $this->secureRenderer = $this->createMock(SecureHtmlRenderer::class);
        $this->urlBuilder = $this->getMockForAbstractClass(UrlInterface::class);

        $context = $this->createMock(Context::class);
        $context->method('getUrlBuilder')->willReturn($this->urlBuilder);

        $this->block = new NewCategory(
            $context,
            $this->registry,
            $this->formFactory,
            $this->jsonEncoder,
            $this->categoryFactory,
            [],
            $this->secureRenderer
        );
    }

    /**
     * Test _prepareForm creates form with correct structure and fields.
     *
     * @return void
     */
    public function testPrepareFormCreatesFormWithAllFields(): void
    {
        $form = $this->createMock(Form::class);
        $fieldset = $this->createMock(Fieldset::class);
        $noteField = $this->createMock(Note::class);
        $nameField = $this->createMock(Text::class);
        $parentField = $this->createMock(Select::class);

        $this->formFactory->expects($this->once())
            ->method('create')
            ->with([
                'data' => [
                    'id' => 'new_category_form',
                    'class' => 'admin__scope-old'
                ]
            ])
            ->willReturn($form);

        $form->expects($this->once())
            ->method('addField')
            ->with('new_category_messages', 'note', [])
            ->willReturn($noteField);

        $form->expects($this->once())
            ->method('addFieldset')
            ->with('new_category_form_fieldset', [])
            ->willReturn($fieldset);

        $category1 = $this->createMock(Category::class);
        $category1->method('getEntityId')->willReturn(1);
        $category1->method('getName')->willReturn('Root');

        $category2 = $this->createMock(Category::class);
        $category2->method('getEntityId')->willReturn(2);
        $category2->method('getName')->willReturn('Default Category');

        $this->setupCategoryCollectionMock([
            1 => $category1,
            2 => $category2
        ]);

        $fieldset->expects($this->exactly(2))
            ->method('addField')
            ->willReturnCallback(
                function ($id, $type, $config) use ($nameField, $parentField) {
                    if ($id === 'new_category_name') {
                        $this->assertSame('text', $type);
                        $this->assertInstanceOf(Phrase::class, $config['label']);
                        $this->assertSame('Category Name', (string)$config['label']);
                        $this->assertInstanceOf(Phrase::class, $config['title']);
                        $this->assertSame('Category Name', (string)$config['title']);
                        $this->assertTrue($config['required']);
                        $this->assertSame('new_category_name', $config['name']);
                        return $nameField;
                    }
                    if ($id === 'new_category_parent') {
                        $this->assertSame('select', $type);
                        $this->assertInstanceOf(Phrase::class, $config['label']);
                        $this->assertSame('Parent Category', (string)$config['label']);
                        $this->assertInstanceOf(Phrase::class, $config['title']);
                        $this->assertSame('Parent Category', (string)$config['title']);
                        $this->assertTrue($config['required']);
                        $this->assertSame([2 => 'Default Category'], $config['options']);
                        $this->assertSame('validate-parent-category', $config['class']);
                        $this->assertSame('new_category_parent', $config['name']);
                        $this->assertInstanceOf(Phrase::class, $config['note']);
                        $this->assertNotEmpty((string)$config['note']);
                        return $parentField;
                    }
                    return null;
                }
            );

        $this->invokePrepareForm();

        $reflectionProperty = (new ReflectionClass($this->block))->getProperty('_form');
        $assignedForm = $reflectionProperty->getValue($this->block);

        $this->assertSame($form, $assignedForm);
    }

    /**
     * Test _getParentCategoryOptions returns correct options based on category count.
     *
     * @param array $categoryData Array of [id => name] pairs
     * @param array $expectedResult Expected result from _getParentCategoryOptions
     * @return void
     * @dataProvider parentCategoryOptionsDataProvider
     */
    public function testGetParentCategoryOptions(array $categoryData, array $expectedResult): void
    {
        $categories = [];
        foreach ($categoryData as $id => $name) {
            $category = $this->createMock(Category::class);
            $category->method('getEntityId')->willReturn($id);
            $category->method('getName')->willReturn($name);
            $categories[$id] = $category;
        }

        $this->setupCategoryCollectionMock($categories);

        $result = $this->invokeGetParentCategoryOptions();

        if (empty($expectedResult)) {
            $this->assertEmpty($result);
        } else {
            $this->assertSame($expectedResult, $result);
        }
    }

    /**
     * Data provider for testGetParentCategoryOptions.
     *
     * @return array
     */
    public static function parentCategoryOptionsDataProvider(): array
    {
        return [
            'two_categories_returns_second' => [
                'categoryData' => [
                    1 => 'Root',
                    2 => 'Default Category'
                ],
                'expectedResult' => [2 => 'Default Category']
            ],
            'three_categories_returns_empty' => [
                'categoryData' => [
                    1 => 'Root',
                    2 => 'Default Category',
                    3 => 'Custom Category'
                ],
                'expectedResult' => []
            ],
            'one_category_returns_empty' => [
                'categoryData' => [
                    1 => 'Root'
                ],
                'expectedResult' => []
            ],
            'no_categories_returns_empty' => [
                'categoryData' => [],
                'expectedResult' => []
            ]
        ];
    }

    /**
     * Test getSaveCategoryUrl returns correct URL.
     *
     * @return void
     */
    public function testGetSaveCategoryUrl(): void
    {
        $expectedUrl = 'http://example.com/admin/catalog/category/save';

        $this->urlBuilder->expects($this->once())
            ->method('getUrl')
            ->with('catalog/category/save')
            ->willReturn($expectedUrl);

        $result = $this->block->getSaveCategoryUrl();

        $this->assertSame($expectedUrl, $result);
    }

    /**
     * Test getAfterElementHtml returns script tag with widget initialization.
     *
     * @return void
     */
    public function testGetAfterElementHtml(): void
    {
        $suggestUrl = 'http://example.com/admin/catalog/category/suggestCategories';
        $saveUrl = 'http://example.com/admin/catalog/category/save';

        $expectedOptionsArray = [
            'suggestOptions' => [
                'source' => $suggestUrl,
                'valueField' => '#new_category_parent',
                'className' => 'category-select',
                'multiselect' => true,
                'showAll' => true,
            ],
            'saveCategoryUrl' => $saveUrl,
        ];

        $encodedOptions = json_encode($expectedOptionsArray);

        $this->urlBuilder->expects($this->exactly(2))
            ->method('getUrl')
            ->willReturnCallback(function ($route) use ($suggestUrl, $saveUrl) {
                if ($route === 'catalog/category/suggestCategories') {
                    return $suggestUrl;
                }
                if ($route === 'catalog/category/save') {
                    return $saveUrl;
                }
                return '';
            });

        $this->jsonEncoder->expects($this->once())
            ->method('encode')
            ->with($expectedOptionsArray)
            ->willReturn($encodedOptions);

        // @codingStandardsIgnoreStart
        // phpcs:disable Magento2.Templates.InlineJs
        $expectedScript = <<<HTML
require(["jquery","mage/mage"],function($) {  // waiting for dependencies at first
    $(function(){ // waiting for page to load to have '#category_ids-template' available
        $('#new-category').mage('newCategoryDialog', {$encodedOptions});
    });
});
HTML;
        // phpcs:enable Magento2.Templates.InlineJs
        // @codingStandardsIgnoreEnd

        $this->secureRenderer->expects($this->once())
            ->method('renderTag')
            ->with('script', [], $expectedScript, false)
            ->willReturn('<script type="text/x-magento-init">' . $expectedScript . '</script>');

        $result = $this->block->getAfterElementHtml();

        $this->assertStringContainsString('<script type="text/x-magento-init">', $result);
        $this->assertStringContainsString('newCategoryDialog', $result);
        $this->assertStringContainsString($encodedOptions, $result);
    }

    /**
     * Test constructor sets use container to true.
     *
     * @return void
     */
    public function testConstructorSetsUseContainer(): void
    {
        $this->assertTrue($this->block->getData('use_container'));
    }

    /**
     * Invoke protected _prepareForm method.
     *
     * @return void
     */
    private function invokePrepareForm(): void
    {
        $reflection = new ReflectionClass($this->block);
        $method = $reflection->getMethod('_prepareForm');
        $method->invoke($this->block);
    }

    /**
     * Invoke protected _getParentCategoryOptions method.
     *
     * @return array
     */
    private function invokeGetParentCategoryOptions(): array
    {
        $reflection = new ReflectionClass($this->block);
        $method = $reflection->getMethod('_getParentCategoryOptions');
        return $method->invoke($this->block);
    }

    /**
     * Set up category collection mock with given items.
     *
     * @param array $items
     * @return void
     */
    private function setupCategoryCollectionMock(array $items): void
    {
        $collection = $this->createMock(Collection::class);
        $category = $this->createMock(Category::class);

        $this->categoryFactory->expects($this->once())
            ->method('create')
            ->willReturn($category);

        $category->expects($this->once())
            ->method('getCollection')
            ->willReturn($collection);

        $collection->expects($this->once())
            ->method('addAttributeToSelect')
            ->with('name')
            ->willReturnSelf();

        $collection->expects($this->once())
            ->method('addAttributeToSort')
            ->with('entity_id', 'ASC')
            ->willReturnSelf();

        $collection->expects($this->once())
            ->method('setPageSize')
            ->with(3)
            ->willReturnSelf();

        $collection->expects($this->once())
            ->method('load')
            ->willReturnSelf();

        $collection->expects($this->once())
            ->method('getItems')
            ->willReturn($items);
    }
}
