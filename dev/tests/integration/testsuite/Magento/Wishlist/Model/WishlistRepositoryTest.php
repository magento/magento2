<?php

namespace Magento\Wishlist\Model;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\TestFramework\ObjectManager;
use Magento\Wishlist\Api\Data\WishlistInterface;
use Magento\Wishlist\Api\WishlistRepositoryInterface;

class WishlistRepositoryTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var WishlistRepositoryInterface
     */
    private $wishlistRepository;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /** @var FilterBuilder */
    private $filterBuilder;

    /** @var SearchCriteriaBuilder */
    private $searchCriteriaBuilder;


    /**
     * {@inheritDoc}
     */
    protected function setUp()
    {
        $this->objectManager = ObjectManager::getInstance();
        $this->wishlistRepository = $this->objectManager->get(WishlistRepositoryInterface::class);
        $this->customerRepository = $this->objectManager->get(CustomerRepositoryInterface::class);
        $this->searchCriteriaBuilder = $this->objectManager->create(
            \Magento\Framework\Api\SearchCriteriaBuilder::class
        );
        $this->filterBuilder = $this->objectManager->get(
            \Magento\Framework\Api\FilterBuilder::class
        );
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testSave()
    {
        /** @var WishlistInterface $wishlist */
        $wishlist = $this->objectManager->get(WishlistInterface::class);
        /** @var CustomerInterface $customer */
        $customer = $this->customerRepository->save($this->getCustomer());
        $wishlist->setCustomerId($customer->getId());
        $wishlist = $this->wishlistRepository->save($wishlist);
        $this->assertNotFalse($wishlist->getId());
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testDelete()
    {
        /** @var WishlistInterface $wishlist */
        $wishlist = $this->objectManager->get(WishlistInterface::class);
        /** @var CustomerInterface $customer */
        $customer = $this->customerRepository->save($this->getCustomer());
        $wishlist->setCustomerId($customer->getId());
        $wishlist = $this->wishlistRepository->save($wishlist);
        $this->assertTrue($this->wishlistRepository->delete($wishlist));
    }

    /**
     * @magentoDbIsolation enabled
     * @magentoDataFixture Magento/Wishlist/_files/wishlist.php
     */
    public function testGetList()
    {

        $filter1 = $this->filterBuilder
            ->setField('sharing_code')
            ->setValue('fixture_unique_code')
            ->create();

        $this->searchCriteriaBuilder->addFilters([$filter1]);
        $searchCriteria = $this->searchCriteriaBuilder->create();

        $results = $this->wishlistRepository->getList($searchCriteria);
        $this->assertEquals($results->getTotalCount(), 1);

    }

    /**
     * @return CustomerInterface
     */
    private function getCustomer(): CustomerInterface
    {
        /** @var CustomerInterface $customer */
        $customer = $this->objectManager->get(CustomerInterface::class);
        $customer->setFirstname('Test');
        $customer->setLastname('Customer');
        $customer->setEmail( 'test@customer.test');
        return $customer;
    }


}
