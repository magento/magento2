<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\CatalogRule\Controller\Adminhtml\Promo\Catalog;

use Magento\Framework\Message\ManagerInterface;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorageManager as FixtureManager;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\AbstractBackendController;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\CatalogRule\Test\Fixture\Rule as CatalogRuleFixture;
use Magento\CatalogRule\Api\CatalogRuleRepositoryInterface;

#[
    AppArea('adminhtml')
]
class MassActionTest extends AbstractBackendController
{
    /**
     * @var CatalogRuleRepositoryInterface
     */
    private $model;

    protected function setUp(): void
    {
        parent::setUp();
        $objectManager = Bootstrap::getObjectManager();
        $this->model = $objectManager->get(CatalogRuleRepositoryInterface::class);
    }

    #[
        DataFixture(CatalogRuleFixture::class, ['is_active' => 0], 'cr1'),
        DataFixture(CatalogRuleFixture::class, ['is_active' => 0], 'cr2'),
    ]
    public function testMassActivatedRule()
    {
        $rule1 = FixtureManager::getStorage()->get('cr1');
        $rule2 = FixtureManager::getStorage()->get('cr2');
        $beforeActivateRule1 = $this->model->get($rule1->getId());
        self::assertEquals(0, $beforeActivateRule1->getIsActive());
        $beforeActivateRule2 = $this->model->get($rule2->getId());
        self::assertEquals(0, $beforeActivateRule2->getIsActive());
        $params = ['catalogpricerule' => [$rule1->getId(), $rule2->getId()]];
        $request = $this->getRequest();
        $request->setParams($params);
        $request->setMethod(HttpRequest::METHOD_POST);
        $url = 'backend/catalog_rule/promo_catalog/massActivate';
        $this->dispatch($url);
        $rule1 = $this->model->get($rule1->getId());
        $afterActivateRule1 = $this->model->get($rule1->getId());
        $afterActivateRule2 = $this->model->get($rule2->getId());
        self::assertEquals('You activated a total of 2 records.', $this->getSuccessMessage());
        self::assertEquals(1, $afterActivateRule1->getIsActive());
        self::assertEquals(1, $afterActivateRule2->getIsActive());
    }

    #[
        DataFixture(CatalogRuleFixture::class, ['is_active' => 1], 'cr1'),
        DataFixture(CatalogRuleFixture::class, ['is_active' => 1], 'cr2'),
    ]
    public function testMassInActivatedRule()
    {
        $rule1 = FixtureManager::getStorage()->get('cr1');
        $rule2 = FixtureManager::getStorage()->get('cr2');
        $beforeActivateRule1 = $this->model->get($rule1->getId());
        self::assertEquals(1, $beforeActivateRule1->getIsActive());
        $beforeActivateRule2 = $this->model->get($rule2->getId());
        self::assertEquals(1, $beforeActivateRule2->getIsActive());
        $params = ['catalogpricerule' => [$rule1->getId(), $rule2->getId()]];
        $request = $this->getRequest();
        $request->setParams($params);
        $request->setMethod(HttpRequest::METHOD_POST);
        $url = 'backend/catalog_rule/promo_catalog/massDeactivate';
        $this->dispatch($url);
        $afterActivateRule1 = $this->model->get($rule1->getId());
        $afterActivateRule2 = $this->model->get($rule2->getId());
        self::assertEquals('You deactivated a total of 2 records.', $this->getSuccessMessage());
        self::assertEquals(0, $afterActivateRule1->getIsActive());
        self::assertEquals(0, $afterActivateRule2->getIsActive());
    }

    #[DataFixture(CatalogRuleFixture::class, ['is_active' => 1], 'cr1')]
    #[DataFixture(CatalogRuleFixture::class, ['is_active' => 1], 'cr2')]
    public function testMassDeleteRule()
    {
        $rule1 = FixtureManager::getStorage()->get('cr1');
        $rule2 = FixtureManager::getStorage()->get('cr2');
        $params = ['catalogpricerule' => [$rule1->getId(), $rule2->getId()]];
        $request = $this->getRequest();
        $request->setParams($params);
        $request->setMethod(HttpRequest::METHOD_POST);
        $url = 'backend/catalog_rule/promo_catalog/massDelete';
        $this->dispatch($url);
        self::assertEquals('A total of 2 record(s) were deleted.', $this->getSuccessMessage());
    }

    /**
     * Gets success message after dispatching the controller.
     *
     * @return string|null
     */
    private function getSuccessMessage(): ?string
    {
        /** @var ManagerInterface $messageManager */
        $messageManager = $this->_objectManager->get(ManagerInterface::class);
        $messages = $messageManager->getMessages(true)->getItems();
        if ($messages) {
            return $messages[0]->getText();
        }
        return null;
    }
}
