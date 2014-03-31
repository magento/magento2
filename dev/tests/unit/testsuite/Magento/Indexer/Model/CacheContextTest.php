<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @copyright   Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

namespace Magento\Indexer\Model;


class CacheContextTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Indexer\Model\CacheContext
     */
    protected $context;

    /**
     * Set up test
     */
    public function setUp()
    {
        $this->context = new \Magento\Indexer\Model\CacheContext();
    }

    /**
     * Test registerEntities
     */
    public function testRegisterEntities()
    {
        $cacheTag = 'tag';
        $expectedIds = array(1, 2, 3);
        $this->context->registerEntities($cacheTag, $expectedIds);
        $actualIds = $this->context->getRegisteredEntity($cacheTag);
        $this->assertEquals($expectedIds, $actualIds);
    }

    /**
     * test getIdentities
     */
    public function testGetIdentities()
    {
        $expectedIdentities = array(
            'product_1', 'product_2', 'product_3', 'category_5', 'category_6', 'category_7'
        );
        $productTag = 'product';
        $categoryTag = 'category';
        $productIds = array(1, 2, 3);
        $categoryIds = array(5, 6, 7);
        $this->context->registerEntities($productTag, $productIds);
        $this->context->registerEntities($categoryTag, $categoryIds);
        $actualIdentities = $this->context->getIdentities();
        $this->assertEquals($expectedIdentities, $actualIdentities);
    }
}
