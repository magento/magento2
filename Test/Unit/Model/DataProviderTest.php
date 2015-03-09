<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\AdvancedSearch\Test\Unit\Model;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class DataProviderTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    protected function setUp()
    {
        $this->objectManager = new ObjectManager($this);
    }

    public function testGetRecommendations()
    {
        /** @var \Magento\Search\Model\QueryInterface|\PHPUnit_Framework_MockObject_MockObject $searchQuery */
        $searchQuery = $this->getMockBuilder('\Magento\Search\Model\QueryInterface')->getMockForAbstractClass();
        /** @var \Magento\AdvancedSearch\Model\SuggestedQueries $dataProvider */
        $dataProvider = $this->objectManager->getObject('Magento\AdvancedSearch\Model\SuggestedQueries');
        $this->assertEquals([], $dataProvider->getItems($searchQuery));
    }

    public function testIsResultsCountEnabled()
    {
        /** @var \Magento\AdvancedSearch\Model\SuggestedQueries $dataProvider */
        $dataProvider = $this->objectManager->getObject('Magento\AdvancedSearch\Model\SuggestedQueries');
        $this->assertFalse($dataProvider->isResultsCountEnabled());
    }
}
