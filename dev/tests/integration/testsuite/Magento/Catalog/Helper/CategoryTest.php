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
namespace Magento\Catalog\Helper;

class CategoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Catalog\Helper\Category
     */
    protected $_helper;

    protected function setUp()
    {
        $this->_helper = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->get(
            'Magento\Catalog\Helper\Category'
        );
    }

    protected function tearDown()
    {
        if ($this->_helper) {
            $helperClass = get_class($this->_helper);
            /** @var $objectManager \Magento\TestFramework\ObjectManager */
            $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
            $objectManager->get('Magento\Framework\Registry')->unregister('_helper/' . $helperClass);
        }
        $this->_helper = null;
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/categories.php
     */
    public function testGetStoreCategories()
    {
        $categories = $this->_helper->getStoreCategories();
        $this->assertInstanceOf('Magento\Framework\Data\Tree\Node\Collection', $categories);
        $index = 0;
        $expectedPaths = array(
            array(3, '1/2/3'),
            array(6, '1/2/6'),
            array(7, '1/2/7'),
            array(9, '1/2/9'),
            array(10, '1/2/10'),
            array(11, '1/2/11'),
            array(12, '1/2/12')
        );
        foreach ($categories as $category) {
            $this->assertInstanceOf('Magento\Framework\Data\Tree\Node', $category);
            $this->assertEquals($expectedPaths[$index][0], $category->getId());
            $this->assertEquals($expectedPaths[$index][1], $category->getData('path'));
            $index++;
        }
    }

    public function testGetCategoryUrl()
    {
        $url = 'http://example.com/';
        $category = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Category',
            array('data' => array('url' => $url))
        );
        $this->assertEquals($url, $this->_helper->getCategoryUrl($category));

        $category = new \Magento\Framework\Object(array('url' => $url));
        $this->assertEquals($url, $this->_helper->getCategoryUrl($category));
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/categories.php
     */
    public function testCanShow()
    {
        // by ID of a category that is not a root
        $this->assertTrue($this->_helper->canShow(7));
    }

    public function testCanShowFalse()
    {
        /** @var $category \Magento\Catalog\Model\Category */
        $category = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Category'
        );
        $this->assertFalse($this->_helper->canShow($category));
        $category->setId(1);
        $this->assertFalse($this->_helper->canShow($category));
        $category->setIsActive(true);
        $this->assertFalse($this->_helper->canShow($category));
    }

    public function testCanUseCanonicalTagDefault()
    {
        $this->assertEquals(0, $this->_helper->canUseCanonicalTag());
    }

    /**
     * @magentoConfigFixture current_store catalog/seo/category_canonical_tag 1
     */
    public function testCanUseCanonicalTag()
    {
        $this->assertEquals(1, $this->_helper->canUseCanonicalTag());
    }
}
