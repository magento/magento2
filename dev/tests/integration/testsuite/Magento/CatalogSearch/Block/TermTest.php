<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\CatalogSearch\Block;

class TermTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Search\Block\Term
     */
    protected $_block;

    protected function setUp()
    {
        $this->_block = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Framework\View\LayoutInterface'
        )->createBlock(
            'Magento\Search\Block\Term'
        );
    }

    public function testGetSearchUrl()
    {
        $query = uniqid();
        $obj = new \Magento\Framework\DataObject(['name' => $query]);
        $this->assertStringEndsWith("/catalogsearch/result/?q={$query}", $this->_block->getSearchUrl($obj));
    }
}
