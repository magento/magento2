<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Elasticsearch\Test\Unit\Model\Dataprovider;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class SuggestionsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
    }

    public function testGetItems()
    {
        /** @var \Magento\Search\Model\QueryInterface|\PHPUnit_Framework_MockObject_MockObject $searchQuery */
        $searchQuery = $this->getMockBuilder('\Magento\Search\Model\QueryInterface')->getMockForAbstractClass();
        /** @var \Magento\AdvancedSearch\Model\SuggestedQueries $dataProvider */
        $dataProvider = $this->objectManager->getObject('Magento\Elasticsearch\Model\DataProvider\Suggestions');
        $this->assertEquals([], $dataProvider->getItems($searchQuery));
    }
}
