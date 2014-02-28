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

namespace Magento\Customer\Block\Adminhtml\Group;

use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Customer\Service\V1\Dto\CustomerGroup;
use Magento\Customer\Service\V1\Dto\Filter;
use Magento\Customer\Service\V1\Dto\SearchCriteria;
use Magento\Customer\Service\V1\Dto\SearchCriteriaBuilder;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\AbstractController;

/**
 * Magento\Customer\Block\Adminhtml\Group\Edit
 *
 * @magentoAppArea adminhtml
 */
class EditTest extends AbstractController
{
    /** @var \Magento\View\LayoutInterface */
    private $layout;

    /** @var \Magento\Customer\Service\V1\CustomerGroupService */
    private $customerGroupService;

    /** @var \Magento\Registry */
    private $registry;

    public function setUp()
    {
        parent::setUp();
        $this->layout = Bootstrap::getObjectManager()->create(
            'Magento\Core\Model\Layout',
            ['area' => FrontNameResolver::AREA_CODE]
        );
        $this->customerGroupService = Bootstrap::getObjectManager()->create(
            'Magento\Customer\Service\V1\CustomerGroupService'
        );

        $this->registry = Bootstrap::getObjectManager()->get('Magento\Registry');
    }

    public function tearDown()
    {
        $this->registry->unregister('current_group');
    }

    public function testDeleteButtonNotExistInDefaultGroup()
    {
        $customerGroup = $this->customerGroupService->getDefaultGroup(0);
        $this->registry->register('current_group', $customerGroup);
        $this->getRequest()->setParam('id', $customerGroup->getId());

        /** @var $block Edit */
        $block = $this->layout->createBlock('Magento\Customer\Block\Adminhtml\Group\Edit', 'block');
        $buttonsHtml = $block->getButtonsHtml();

        $this->assertNotContains('delete', $buttonsHtml);
    }

    /**
     * @magentoDataFixture Magento/Customer/_files/customer_group.php
     */
    public function testDeleteButtonExistInCustomGroup()
    {
        $searchCriteria = (new SearchCriteriaBuilder())
            ->addFilter(new Filter([
                'field'             => 'code',
                'value'             => 'custom_group',
            ]))
            ->create();
        /** @var CustomerGroup $customerGroup */
        $customerGroup = $this->customerGroupService->searchGroups($searchCriteria)->getItems()[0];
        $this->getRequest()->setParam('id', $customerGroup->getId());
        $this->registry->register('current_group', $customerGroup);

        /** @var $block Edit */
        $block = $this->layout->createBlock('Magento\Customer\Block\Adminhtml\Group\Edit', 'block');
        $buttonsHtml = $block->getButtonsHtml();

        $this->assertContains('delete', $buttonsHtml);
    }
}
