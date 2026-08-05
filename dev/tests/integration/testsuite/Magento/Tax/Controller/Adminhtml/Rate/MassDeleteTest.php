<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Tax\Controller\Adminhtml\Rate;

use Magento\Framework\Exception\LocalizedException;
use Magento\TestFramework\Fixture\AppArea;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\TestCase\AbstractBackendController;
use Magento\Tax\Test\Fixture\TaxRate as TaxRateFixture;
use Magento\Tax\Model\Calculation\RateFactory;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Message\ManagerInterface;

/**
 * Test class for mass delete tax rate in admin grid
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
#[
    AppArea('adminhtml')
]
class MassDeleteTest extends AbstractBackendController
{
    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @var RateFactory
     */
    private $rateFactory;

    /**
     * @throws LocalizedException
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->fixtures = DataFixtureStorageManager::getStorage();
        $this->rateFactory = $this->_objectManager->create(RateFactory::class);
    }

    #[
        DataFixture(
            TaxRateFixture::class,
            [
                'tax_country_id' => 'US',
                'code' => 'Test Rate US',
                'rate' => '10',
            ],
            'tax_rate_us',
        ),
        DataFixture(
            TaxRateFixture::class,
            [
                'tax_country_id' => 'DE',
                'code' => 'Test Rate DE',
                'rate' => '21',
            ],
            'tax_rate_de',
        ),
    ]
    public function testMassDelete(): void
    {
        $rate1 = $this->fixtures->get('tax_rate_us');
        $rate2 = $this->fixtures->get('tax_rate_de');
        self::assertNotNull($rate1->getId());
        $params = ['tax_rate_ids' => [$rate1->getId()]];
        $request = $this->getRequest();
        $request->setParams($params);
        $request->setMethod(HttpRequest::METHOD_POST);
        $url = 'backend/tax/rate/massDelete';
        $this->dispatch($url);
        self::assertEquals('A total of 1 record(s) have been deleted.', $this->getSuccessMessage());
        $afterDeleteUsRate = $this->rateFactory->create()->load($rate1->getId());
        self::assertNull($afterDeleteUsRate->getId());
        self::assertNotNull($rate2->getId());
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
