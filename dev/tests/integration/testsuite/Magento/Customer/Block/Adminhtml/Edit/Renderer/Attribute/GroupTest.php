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
        /** @var \Magento\Customer\Service\V1\CustomerMetadataServiceInterface $metadataService */
        $metadataService = Bootstrap::getObjectManager()->get(
            'Magento\Customer\Service\V1\CustomerMetadataServiceInterface'
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
