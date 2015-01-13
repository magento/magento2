<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Customer\Block\Adminhtml\Edit\Renderer\Attribute;

use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\TestFramework\Helper\Bootstrap;

/**
 * Test Magento\Customer\Block\Adminhtml\Edit\Renderer\Attribute\Group
 *
 * @magentoAppArea adminhtml
 */
class GroupTest extends \PHPUnit_Framework_TestCase
{
    /** @var Group */
    private $groupRenderer;

    /** @var AbstractElement */
    private $groupElement;

    public function setUp()
    {
        /** @var \Magento\Customer\Block\Adminhtml\Edit\Tab\Account $accountBlock */
        $accountBlock = Bootstrap::getObjectManager()->get('Magento\Customer\Block\Adminhtml\Edit\Tab\Account');
        $accountBlock->initForm();
        $this->groupElement = $accountBlock->getForm()->getElement('group_id');

        $this->groupRenderer = Bootstrap::getObjectManager()->create(
            'Magento\Customer\Block\Adminhtml\Edit\Renderer\Attribute\Group'
        );
    }

    public function testRender()
    {
        /** @var \Magento\Customer\Api\CustomerMetadataInterface $metadataService */
        $metadataService = Bootstrap::getObjectManager()->get(
            'Magento\Customer\Api\CustomerMetadataInterface'
        );
        $autoGroupAttribute = $metadataService->getAttributeMetadata('disable_auto_group_change');
        $this->groupRenderer->setDisableAutoGroupChangeAttribute($autoGroupAttribute);

        $html = $this->groupRenderer->render($this->groupElement);

        $this->assertContains('<option value="1">General</option>', $html);
        $this->assertContains('<option value="2">Wholesale</option>', $html);
        $this->assertContains('<option value="3">Retailer</option>', $html);
        $this->assertContains('<span>Group</span>', $html);
        $this->assertContains('Disable Automatic Group Change Based on VAT ID', $html);
    }

    public function testRenderWithoutAutoGroup()
    {
        $html = $this->groupRenderer->render($this->groupElement);

        $this->assertContains('<option value="1">General</option>', $html);
        $this->assertContains('<option value="2">Wholesale</option>', $html);
        $this->assertContains('<option value="3">Retailer</option>', $html);
        $this->assertContains('<span>Group</span>', $html);
        $this->assertNotContains('Disable Automatic Group Change Based on VAT ID', $html);
    }
}
