<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Catalog\Helper;

use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Framework\Phrase;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class OutputTest extends TestCase
{
    /**
     * @var Output
     */
    protected $_helper;

    protected function setUp(): void
    {
        $this->_helper = Bootstrap::getObjectManager()->get(
            Output::class
        );
    }

    /**
     * addHandler()
     * getHandlers()
     */
    public function testAddHandlerGetHandlers()
    {
        // invalid handler
        $this->_helper->addHandler('method', 'handler');
        $this->assertEquals([], $this->_helper->getHandlers('method'));

        // add one handler
        $objectOne = new \StdClass();
        $this->_helper->addHandler('valid', $objectOne);
        $this->assertSame([$objectOne], $this->_helper->getHandlers('valid'));

        // add another one
        $objectTwo = new \StdClass();
        $this->_helper->addHandler('valid', $objectTwo);
        $this->assertSame([$objectOne, $objectTwo], $this->_helper->getHandlers('valid'));
    }

    public function testProcess()
    {
        $this->_helper->addHandler('sampleProcessor', $this);
        $this->assertStringStartsWith(__CLASS__, $this->_helper->process('sampleProcessor', uniqid(), []));
    }

    public function testProductAttribute()
    {
        $this->_testAttribute(
            'productAttribute',
            Product::ENTITY,
            "&lt;p&gt;line1&lt;/p&gt;<br />\nline2"
        );
    }

    public function testCategoryAttribute()
    {
        $this->_testAttribute(
            'categoryAttribute',
            Category::ENTITY,
            "&lt;p&gt;line1&lt;/p&gt;\nline2"
        );
    }

    /**
     * Tests if string has directives.
     *
     * @dataProvider isDirectiveDataProvider
     * @param string|Phrase $html
     * @param bool $expectedResult
     */
    public function testIsDirectivesExists($html, bool $expectedResult): void
    {
        $this->assertEquals($expectedResult, $this->_helper->isDirectivesExists($html));
    }

    /**
     * Data provider for testIsDirectivesExists()
     *
     * @return array
     */
    public function isDirectiveDataProvider(): array
    {
        return [
            'attribute_html_without_directive' => ['Test string', false],
            'attribute_html_with_incorrect_directive' => ['{store url="customer/account/login"}', false],
            'attribute_html_with_correct_directive' => ['{{store url="customer/account/login"}}', true],
            'attribute_html_with_object_type' => [__('{{store url="%1"}}', 'customer/account/login'), true],
        ];
    }

    /**
     * Helper method for testProcess()
     *
     * @param Output $helper
     * @param string $string
     * @param mixed $params
     * @return string
     * @see testProcess()
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function sampleProcessor(Output $helper, $string, $params)
    {
        return __CLASS__ . $string;
    }

    /**
     * Test productAttribute() or categoryAttribute() method
     *
     * @param string $method
     * @param string $entityCode
     * @param string $expectedResult
     * @throws \Exception on assertion failure
     */
    protected function _testAttribute($method, $entityCode, $expectedResult)
    {
        $attributeName = 'description';
        $attribute = Bootstrap::getObjectManager()->get(
            \Magento\Eav\Model\Config::class
        )->getAttribute(
            $entityCode,
            $attributeName
        );
        $isHtml = $attribute->getIsHtmlAllowedOnFront();
        $isWysiwyg = $attribute->getIsWysiwygEnabled();
        $attribute->setIsHtmlAllowedOnFront(0)->setIsWysiwygEnabled(0);

        try {
            $this->assertEquals(
                $expectedResult,
                $this->_helper->{$method}(uniqid(), __("<p>line1</p>\nline2"), $attributeName)
            );

            $attribute->setIsHtmlAllowedOnFront($isHtml)->setIsWysiwygEnabled($isWysiwyg);
        } catch (\Exception $e) {
            $attribute->setIsHtmlAllowedOnFront($isHtml)->setIsWysiwygEnabled($isWysiwyg);
            throw $e;
        }
    }
}
