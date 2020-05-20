<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Block;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Text;
use Magento\Framework\View\LayoutInterface;
use Magento\Search\Model\QueryFactory;
use Magento\TestFramework\Helper\Bootstrap;

class ResultTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var LayoutInterface
     */
    private $layout;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->layout = $this->objectManager->get(LayoutInterface::class);
    }

    public function testSetListOrders()
    {
        $this->layout->addBlock(Text::class, 'head');
        // The tested block is using head block
        /** @var $block Result */
        $block = $this->layout->addBlock(Result::class, 'block');
        $childBlock = $this->layout->addBlock(Text::class, 'search_result_list', 'block');

        $this->assertSame($childBlock, $block->getListBlock());
    }

    /**
     * Verify search value escaping process
     *
     * @magentoConfigFixture default/catalog/search/engine elasticsearch6
     * @dataProvider toEscapeSearchTextDataProvider
     * @magentoAppArea frontend
     * @param string $searchValue
     * @param string $expectedOutput
     * @param string $unexpectedOutput
     * @return void
     */
    public function testEscapeSearchText(string $searchValue, string $expectedOutput, string $unexpectedOutput): void
    {
        /** @var Result $searchResultBlock */
        $searchResultBlock = $this->layout->createBlock(Result::class);
        /** @var Template $searchBlock */
        $searchBlock = $this->layout->createBlock(Template::class);
        $searchBlock->setTemplate('Magento_Search::form.mini.phtml');
        /** @var RequestInterface $request */
        $request = $this->objectManager->get(RequestInterface::class);

        $request->setParam(QueryFactory::QUERY_VAR_NAME, $searchValue);
        $searchHtml = $searchBlock->toHtml();

        $this->assertStringContainsString('value=' . '"' . $expectedOutput . '"', $searchHtml);
        $this->assertStringNotContainsString($unexpectedOutput, $searchHtml);

        $resultTitle = $searchResultBlock->getSearchQueryText()->render();
        $this->assertStringContainsString("Search results for: '{$expectedOutput}'", $resultTitle);
        $this->assertStringNotContainsString($unexpectedOutput, $resultTitle);
    }

    /**
     * DataProvider for testEscapeSearchText()
     *
     * @return array
     */
    public function toEscapeSearchTextDataProvider(): array
    {
        return [
            'less_than_sign_escaped' => ['<', '&lt;', '&amp;lt&#x3B;'],
            'greater_than_sign_escaped' => ['>', '&gt;', '&amp;gt&#x3B;'],
            'ampersand_sign_escaped' => ['&', '&amp;', '&amp;amp&#x3B;'],
            'double_quote_sign_escaped' => ['"', '&quot;', '&amp;quot&#x3B;'],
            'single_quote_sign_escaped' => ["'", '&#039;', '&amp;&#x23;039&#x3B;'],
            'plus_sign_not_escaped' => ['+', '+', '&amp;+&#x3B;'],
            'characters_not_escaped' => ['abc', 'abc', '&amp;abc&#x3B;'],
            'numbers_not_escaped' => ['123', '123', '&amp;123&#x3B;'],
        ];
    }
}
