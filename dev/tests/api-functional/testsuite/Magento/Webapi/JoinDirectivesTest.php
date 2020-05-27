<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Webapi;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Api\SortOrderBuilder;

/**
 * Test join directives.
 */
class JoinDirectivesTest extends \Magento\TestFramework\TestCase\WebapiAbstract
{
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchBuilder;

    /**
     * @var SortOrderBuilder
     */
    private $sortOrderBuilder;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var \Magento\User\Model\User
     */
    private $user;

    protected function setUp()
    {
        $objectManager = \Magento\TestFramework\Helper\Bootstrap::getObjectManager();
        $this->searchBuilder = $objectManager->create(\Magento\Framework\Api\SearchCriteriaBuilder::class);
        $this->sortOrderBuilder = $objectManager->create(\Magento\Framework\Api\SortOrderBuilder::class);
        $this->filterBuilder = $objectManager->create(\Magento\Framework\Api\FilterBuilder::class);
        $this->user = $objectManager->create(\Magento\User\Model\User::class);
    }



    /**
     * Retrieve the admin user's information.
     *
     * @return array
     */
    private function getExpectedExtensionAttributes()
    {
        $this->user->load(1);
        return [
            'firstname' => $this->user->getFirstname(),
            'lastname' => $this->user->getLastname(),
            'email' => $this->user->getEmail()
        ];
    }
}
