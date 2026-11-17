<?php
/**
 * Copyright 2019 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Sales\Cron;

use Magento\Backend\Model\Session\Quote as AdminQuoteSession;
use Magento\Customer\Test\Fixture\Customer as CustomerFixture;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ResourceConnection;
use Magento\Quote\Api\GuestCartManagementInterface;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;
use Magento\Quote\Model\QuoteRepository;
use Magento\Quote\Model\ResourceModel\Quote\CollectionFactory as QuoteCollectionFactory;
use Magento\Quote\Test\Fixture\CustomerCart as CustomerCartFixture;
use Magento\Quote\Test\Fixture\GuestCart as GuestCartFixture;
use Magento\Sales\Model\AdminOrder\Create as AdminOrderCreate;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test for Magento\Sales\Cron\CleanExpiredQuotes class.
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 */
class CleanExpiredQuotesTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var CleanExpiredQuotes
     */
    private $cleanExpiredQuotes;

    /**
     * @var QuoteCollectionFactory
     */
    private $quoteCollectionFactory;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    private $objectManager;

    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->cleanExpiredQuotes = $this->objectManager->get(CleanExpiredQuotes::class);
        $this->quoteCollectionFactory = $this->objectManager->get(QuoteCollectionFactory::class);
        $this->resourceConnection = $this->objectManager->get(ResourceConnection::class);
        $this->fixtures = $this->objectManager->get(DataFixtureStorageManager::class)->getStorage();
    }

    /**
     * Check if outdated quotes are deleted.
     *
     * @magentoConfigFixture default_store checkout/cart/delete_quote_after -365
     * @magentoDataFixture Magento/Sales/_files/quotes.php
     */
    public function testExecute()
    {
        //Initial count - should be equal to stores number.
        $this->assertQuotesCount(2);

        //Deleting expired quotes
        $this->cleanExpiredQuotes->execute();

        //Only 1 will be deleted for the store that has all of them expired by config (default_store)
        $this->assertQuotesCount(1);
    }

    /**
     * Check if outdated quotes are deleted.
     *
     * @magentoConfigFixture default_store checkout/cart/delete_quote_after -365
     * @magentoDataFixture Magento/Sales/_files/quotes_big_amount.php
     */
    public function testExecuteWithBigAmountOfQuotes()
    {
        //Initial count - should be equal to 1000
        $this->assertQuotesCount(6000);

        //Deleting expired quotes
        $this->cleanExpiredQuotes->execute();

        //There should be no quotes anymore
        $this->assertQuotesCount(0);
    }

    /**
     * Genuinely stale quotes must be removed while quotes that are still within the
     * configured lifetime must survive the cleanup - regardless of whether they are a
     * storefront cart (guest or customer) or a draft cart created through the admin
     * "Create New Order" flow.
     *
     * This guards the batched/bulk delete introduced for performance (ACP2E-4891): since
     * the deletion is now a single bulk SQL statement driven purely by the id range
     * returned from ExpiredQuotesCollection, it must not sweep up recently touched quotes
     * of any origin.
     *
     * Two stale quotes are used deliberately, one created before and one created after
     * the recent carts, so the recent carts' ids fall between the lowest and highest
     * expired ids. This guards against a range-over-deletion regression (e.g. a bulk
     * "DELETE ... WHERE id BETWEEN min AND max" instead of an exact id match), which
     * would incorrectly remove the recent carts even though only the two stale quotes
     * are actually expired.
     *
     * @magentoAppArea adminhtml
     * @magentoConfigFixture default_store checkout/cart/delete_quote_after 2
     * @return void
     */
    #[
        DataFixture(CustomerFixture::class, as: 'customer'),
        DataFixture(GuestCartFixture::class, as: 'staleStorefrontCart'),
        DataFixture(CustomerCartFixture::class, ['customer_id' => '$customer.id$'], 'activeStorefrontCart'),
    ]
    public function testActiveStorefrontAndAdminCartsRemainAfterCleanup(): void
    {
        // Stale storefront (guest) cart created first: last touched well before the
        // configured lifetime -> must be deleted. Has the lowest id of the quotes
        // involved in this test.
        $staleStorefrontQuoteId = (int)$this->fixtures->get('staleStorefrontCart')->getId();
        $this->setQuoteUpdatedAt($staleStorefrontQuoteId, '-5 days');

        // Active storefront (customer) cart: just created/touched -> must survive.
        $activeStorefrontQuoteId = (int)$this->fixtures->get('activeStorefrontCart')->getId();

        // Active admin cart: built through the backend order-creation flow -> must survive.
        $adminCartId = $this->createActiveAdminCart((int)$this->fixtures->get('customer')->getId());

        // A second stale storefront (guest) cart created last, after both recent carts,
        // so it ends up with the highest id of the quotes involved in this test.
        $staleStorefrontQuoteIdAfter = $this->createStaleGuestCart();
        $this->setQuoteUpdatedAt($staleStorefrontQuoteIdAfter, '-5 days');

        $this->cleanExpiredQuotes->execute();

        $this->assertQuoteExists(
            $activeStorefrontQuoteId,
            true,
            'An active storefront cart must remain after expired-quote cleanup.'
        );
        $this->assertQuoteExists(
            $adminCartId,
            true,
            'An active admin cart must remain after expired-quote cleanup.'
        );
        $this->assertQuoteExists(
            $staleStorefrontQuoteId,
            false,
            'A genuinely stale cart must be removed by expired-quote cleanup.'
        );
        $this->assertQuoteExists(
            $staleStorefrontQuoteIdAfter,
            false,
            'A genuinely stale cart created after the recent carts must also be removed '
            . 'by expired-quote cleanup.'
        );
    }

    /**
     * Optimized assert quotes count
     * Uses collection getSize in order to get quick result
     *
     * @param int $expected
     */
    private function assertQuotesCount(int $expected): void
    {
        $totalCount = $this->quoteCollectionFactory->create()->getSize();
        $this->assertEquals($expected, $totalCount);
    }

    /**
     * Builds a cart through the same backend session/order-creation flow used by the
     * admin "Create New Order" page, simulating an in-progress admin cart.
     *
     * @param int $customerId Id of the customer the admin cart is being created for.
     * @return int Entity id of the persisted admin cart quote.
     */
    private function createActiveAdminCart(int $customerId): int
    {
        $storeId = (int)$this->objectManager->get(StoreManagerInterface::class)->getStore()->getId();

        /** @var AdminQuoteSession $adminSession */
        $adminSession = $this->objectManager->get(AdminQuoteSession::class);
        $adminSession->setStoreId($storeId);
        $adminSession->setCustomerId($customerId);

        /** @var AdminOrderCreate $adminOrderCreate */
        $adminOrderCreate = $this->objectManager->create(AdminOrderCreate::class);
        $adminCartId = (int)$adminOrderCreate->getQuote()->getId();

        self::assertNotEmpty($adminCartId, 'Precondition failed: admin cart quote was not persisted.');

        return $adminCartId;
    }

    /**
     * Creates a new, independent guest cart without relying on the PDF fixture storage.
     *
     * Used to create a second stale quote after the recent carts already exist in this
     * test, which PHP-attribute data fixtures cannot do since they are all applied
     * before the test method body runs.
     *
     * @return int Entity id of the persisted guest cart quote.
     */
    private function createStaleGuestCart(): int
    {
        $maskedId = $this->objectManager->get(GuestCartManagementInterface::class)->createEmptyCart();
        $quoteId = (int)$this->objectManager->get(MaskedQuoteIdToQuoteIdInterface::class)->execute($maskedId);

        self::assertNotEmpty($quoteId, 'Precondition failed: guest cart quote was not persisted.');

        return $quoteId;
    }

    /**
     * Directly backdates a quote's updated_at column, bypassing the resource model's
     * auto-timestamp behavior so the quote can be simulated as stale/expired.
     *
     * @param int $quoteId
     * @param string $dateTimeModifier A relative date/time string (e.g. '-5 days').
     * @return void
     */
    private function setQuoteUpdatedAt(int $quoteId, string $dateTimeModifier): void
    {
        $connection = $this->resourceConnection->getConnection();
        $connection->update(
            $connection->getTableName('quote'),
            ['updated_at' => (new \DateTimeImmutable($dateTimeModifier))->format('Y-m-d H:i:s')],
            ['entity_id = ?' => $quoteId]
        );
    }

    /**
     * Asserts whether a quote with the given id does or does not exist.
     *
     * Queries the quote table directly rather than through QuoteRepository, which
     * caches loaded quotes by id in-memory and would otherwise mask a deletion that
     * happened after the quote was first loaded/saved within the same test.
     *
     * @param int $quoteId
     * @param bool $shouldExist
     * @param string $message
     * @return void
     */
    private function assertQuoteExists(int $quoteId, bool $shouldExist, string $message): void
    {
        $connection = $this->resourceConnection->getConnection();
        $select = $connection->select()
            ->from($connection->getTableName('quote'), ['entity_id'])
            ->where('entity_id = ?', $quoteId);

        $exists = (bool)$connection->fetchOne($select);

        $this->assertSame($shouldExist, $exists, $message);
    }
}
