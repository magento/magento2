<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdvancedSearch\Block;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Search\Model\QueryResult;
use Magento\AdvancedSearch\Model\SuggestedQueriesInterface;
use Magento\Solr\Model\DataProvider\Suggestions as SuggestionsDataProvider;
use Magento\Framework\View\LayoutInterface;

/**
 * @magentoAppArea frontend
 */
class SuggestionsTest extends \PHPUnit_Framework_TestCase
{
    /** @var Suggestions */
    protected $block;

    protected function setUp()
    {
        $suggestions = $this->getMock(SuggestedQueriesInterface::CLASS);
        $suggestions->expects($this->any())->method('getItems')->willReturn([
            new QueryResult('test item', 1),
            new QueryResult("<script>alert('Test');</script>", 1)
        ]);

        Bootstrap::getObjectManager()->removeSharedInstance(SuggestionsDataProvider::CLASS);
        Bootstrap::getObjectManager()->addSharedInstance($suggestions, SuggestionsDataProvider::CLASS);

        $this->block = Bootstrap::getObjectManager()->get(LayoutInterface::CLASS)->createBlock(Suggestions::CLASS);
        $this->block->setNameInLayout('suggestions');
    }

    protected function tearDown()
    {
        Bootstrap::getObjectManager()->removeSharedInstance(SuggestionsDataProvider::CLASS);
    }

    public function testRenderEscaping()
    {
        $html = $this->block->getBlockHtml('suggestions');

        $this->assertContains('test+item', $html);
        $this->assertContains('test item', $html);

        $this->assertNotContains('<script>', $html);
        $this->assertContains('%3Cscript%3Ealert%28%27Test%27%29%3B%3C%2Fscript%3E', $html);
        $this->assertContains("&lt;script&gt;alert('Test');&lt;/script&gt;", $html);
    }
}
