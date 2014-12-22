<?php
/**
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Catalog\Controller\Adminhtml\Category;

use Magento\Framework\Message\MessageInterface;

class DeleteTest extends \Magento\Backend\Utility\Controller
{
    /**
     * @magentoDataFixture Magento/Catalog/_files/categories.php
     */
    public function testDeleteById()
    {
        $categoryId = 4;
        $parentId = 3;
        $this->getRequest()->setParam('id', $categoryId);

        $this->dispatch('backend/catalog/category/delete/');

        $this->assertNull($this->getCategoryById($categoryId));
        $this->assertSessionMessages(
            $this->equalTo(['You deleted the category.']),
            MessageInterface::TYPE_SUCCESS
        );
        $this->assertRedirect($this->stringContains('catalog/category/index/id/' . $parentId));
    }

    /**
     * Retrieve attribute set based on given name.
     *
     * @param int $categoryId
     * @return \Magento\Catalog\Model\Category|null
     */
    protected function getCategoryById($categoryId)
    {
        $category = \Magento\TestFramework\Helper\Bootstrap::getObjectManager()->create(
            'Magento\Catalog\Model\Category'
        )->load($categoryId);
        return $category->getId() === null ? null : $category;
    }
}
