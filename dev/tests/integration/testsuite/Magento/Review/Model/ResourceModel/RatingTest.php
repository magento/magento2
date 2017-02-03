<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Review\Model\ResourceModel;

/**
 * Class RatingTest
 */
class RatingTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @magentoDbIsolation enabled
     */
    protected function setUp()
    {
        $storeId = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get('Magento\Store\Model\StoreManagerInterface')
            ->getStore()->getId();

        $rating = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Review\Model\Rating');
        $rating->setData([
            'rating_code' => 'Test Rating',
            'position' => 0,
            'is_active' => true,
            'entity_id' => 1
        ]);
        $rating->setRatingCodes([$storeId => 'Test Rating']);
        $rating->setStores([$storeId]);
        $rating->save();
        $this->id = $rating->getId();
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testRatingLoad()
    {
        $rating = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Review\Model\Rating');
        $rating->load($this->id);
        $this->assertEquals('Test Rating', $rating->getRatingCode());
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testRatingEdit()
    {
        $rating = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Review\Model\Rating');
        $rating->load($this->id);
        $this->assertEquals('Test Rating', $rating->getRatingCode());
        $storeId = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()
            ->get('Magento\Store\Model\StoreManagerInterface')
            ->getStore()->getId();
        $rating->setRatingCode('Test Rating Edited');
        $rating->setRatingCodes([$storeId => 'Test Rating Edited']);
        $rating->save();

        $this->assertEquals('Test Rating Edited', $rating->getRatingCode());
        $this->assertEquals([$storeId => 'Test Rating Edited'], $rating->getRatingCodes());
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testRatingSaveWithError()
    {
        $this->setExpectedException('Exception', 'Rolled back transaction has not been completed correctly');
        $rating = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create('Magento\Review\Model\Rating');
        $rating->load($this->id);
        $rating->setRatingCodes([222 => 'Test Rating Edited']);
        $rating->save();
    }
}
