<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ConfigurableProduct\Block\Adminhtml\Product\Steps;

use Magento\Backend\Model\Auth\Session;
use Magento\ConfigurableProduct\Block\DataProviders\PermissionsData;
use Magento\Framework\View\Layout;
use Magento\Framework\View\LayoutInterface;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\User\Model\User;
use PHPUnit\Framework\TestCase;

/**
 * @magentoAppArea adminhtml
 * @magentoAppIsolation enabled
 * @magentoDbIsolation enabled
 */
class AttributeValuesTest extends TestCase
{
    /**
     * @magentoDataFixture Magento/ConfigurableProduct/_files/restricted_admin_with_catalog_permissions.php
     */
    public function testRestrictedUserNotAllowedToManageAttributes()
    {
        $user = Bootstrap::getObjectManager()->create(
            User::class
        )->loadByUsername(
            'admincatalog_user'
        );

        /** @var $session Session */
        $session = Bootstrap::getObjectManager()->get(
            Session::class
        );
        $session->setUser($user);

        /** @var $layout Layout */
        $layout = Bootstrap::getObjectManager()->get(
            LayoutInterface::class
        );

        /** @var \Magento\ConfigurableProduct\Block\Adminhtml\Product\Steps\AttributeValues */
        $block = $layout->createBlock(
            AttributeValues::class,
            'step2',
            [
                'data' => [
                    'config' => [
                        'form' => 'product_form.product_form',
                        'modal' => 'configurableModal',
                        'dataScope' => 'productFormConfigurable',
                    ],
                    'permissions' => Bootstrap::getObjectManager()->get(PermissionsData::class)
                ]
            ]
        );
        $isAllowedToManageAttributes = $block->getPermissions()->isAllowedToManageAttributes();
        $html = $block->toHtml();
        $this->assertFalse($isAllowedToManageAttributes);
        $this->assertStringNotContainsString('<button class="action-create-new action-tertiary"', $html);
    }
}
