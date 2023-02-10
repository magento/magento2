<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Catalog\Model\Category;

use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Api\Data\CategoryInterfaceFactory;
use Magento\Catalog\Model\Category\Authorization as CategoryAuthorization;
use Magento\Framework\Authorization;
use Magento\Framework\Exception\AuthorizationException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\ObjectManagerInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

/**
 * Checks authorization for category design attributes edit
 *
 * @magentoDbIsolation enabled
 */
class AuthorizationTest extends TestCase
{
    /** @var ObjectManagerInterface */
    private $objectManager;

    /** @var CategoryAuthorization */
    private $model;

    /** @var CategoryInterfaceFactory */
    private $categoryFactory;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->objectManager = Bootstrap::getObjectManager();
        $this->objectManager->addSharedInstance(
            $this->objectManager->get(AuthorizationMock::class),
            Authorization::class
        );
        $this->model = $this->objectManager->get(CategoryAuthorization::class);
        $this->categoryFactory = $this->objectManager->get(CategoryInterfaceFactory::class);
    }

    /**
     * @inheritdoc
     */
    protected function tearDown(): void
    {
        $this->objectManager->removeSharedInstance(Authorization::class);

        parent::tearDown();
    }

    /**
     * @magentoDataFixture Magento/Catalog/_files/category.php
     *
     * @return void
     */
    public function testAuthorizationWithoutPermissions(): void
    {
        $category = $this->createCategoryWithData(['entity_id' => 333, 'custom_use_parent_settings' => true]);
        $this->expectException(AuthorizationException::class);
        $this->expectExceptionMessage((string)__('Not allowed to edit the category\'s design attributes'));
        $this->model->authorizeSavingOf($category);
    }

    /**
     * @return void
     */
    public function testAuthorizationWithWrongCategoryId(): void
    {
        $wrongCategoryId = 56464654;
        $category = $this->createCategoryWithData(['entity_id' => $wrongCategoryId]);
        $this->expectExceptionObject(NoSuchEntityException::singleField('id', $wrongCategoryId));
        $this->model->authorizeSavingOf($category);
    }

    /**
     * Create category instance with provided data
     *
     * @param array $data
     * @return CategoryInterface
     */
    private function createCategoryWithData(array $data): CategoryInterface
    {
        $category = $this->categoryFactory->create();
        $category->addData($data);

        return $category;
    }
}
