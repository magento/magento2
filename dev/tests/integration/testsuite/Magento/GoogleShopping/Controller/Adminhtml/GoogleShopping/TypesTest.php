<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\GoogleShopping\Controller\Adminhtml\GoogleShopping;

/**
 * @magentoAppArea adminhtml
 */
class TypesTest extends \Magento\Backend\Utility\Controller
{
    public function testIndexAction()
    {
        $this->dispatch('backend/admin/googleshopping_types/index/');
        $body = $this->getResponse()->getBody();
        $this->assertSelectCount('[data-role="row"]', 1, $body, 'Grid with row exists');
    }

    public function testLoadAttributeSetsAction()
    {
        $this->dispatch('backend/admin/googleshopping_types/loadAttributeSets/');
        $body = $this->getResponse()->getBody();

        $this->assertTag(
            [
                'tag'        => 'select',
                'attributes' => ['name' => 'attribute_set_id'],
                'descendant' => [
                    'tag'    => 'option',
                    'attributes' => ['value' => 4],
                    'content' => 'Default',
                ],
            ],
            $body
        );
    }
}
