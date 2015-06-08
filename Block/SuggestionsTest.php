<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AdvancedSearch\Block;

use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test for \Magento\AdvancedSearch\Block\Suggestions
 */
class SuggestionsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\AdvancedSearch\Block\SearchData
     */
    protected $block;

    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        parent::setUp();

        Bootstrap::getObjectManager()->get('Magento\Framework\App\State')->setAreaCode('frontend');
        Bootstrap::getObjectManager()->configure(
            [
                'Magento\AdvancedSearch\Block\Suggestions' => [
                    'arguments' => [
                        'title' => 'Search Data Title',
                    ]
                ],
                'preferences' => [
                    'Magento\Solr\Model\DataProvider\Suggestions' =>
                        'Magento\AdvancedSearch\Block\SuggestedQueriesMock',
                ]
            ]
        );
        $layout = Bootstrap::getObjectManager()->get('Magento\Framework\View\LayoutInterface');
        $this->block = $layout->createBlock('Magento\AdvancedSearch\Block\Suggestions');
        $this->block->setNameInLayout('suggestions');
    }

    /**
     * @return void
     */
    public function testRenderEscaping()
    {
        /** @var \Magento\AdvancedSearch\Block\SuggestedQueriesMock $dataProvider */
        $dataProvider = Bootstrap::getObjectManager()->get('Magento\Solr\Model\DataProvider\Suggestions');

        $dataProvider->setItems(
            [
                'test item',
                "<script>alert('Test');</script>"
            ]
        );

        $html = $this->block->getBlockHtml('suggestions');

        $this->assertContains('test+item', $html);
        $this->assertContains('test item', $html);

        $this->assertNotContains('<script>', $html);
        $this->assertContains('%3Cscript%3Ealert%28%27Test%27%29%3B%3C%2Fscript%3E', $html);
        $this->assertContains("&lt;script&gt;alert('Test');&lt;/script&gt;", $html);
    }
}
