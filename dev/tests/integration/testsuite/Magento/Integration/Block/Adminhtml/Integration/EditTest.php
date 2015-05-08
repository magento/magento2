<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 *
 */

namespace Magento\Integration\Block\Adminhtml\Integration;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\Integration\Model\Integration;

/**
 * Test class for \Magento\Integration\Block\Adminhtml\Integration\Edit
 *
 * @magentoAppArea adminhtml
 * @magentoDbIsolation enabled
 */
class EditTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Magento\Integration\Block\Adminhtml\Integration\Edit
     */
    protected $editBlock;

    protected function setUp()
    {
        $this->editBlock = Bootstrap::getObjectManager()
            ->create('Magento\Integration\Block\Adminhtml\Integration\Edit');
    }

    public function testGetHeaderTextNewIntegration()
    {
        $this->assertEquals('New Integration', $this->editBlock->getHeaderText()->getText());
    }

    public function testGetHeaderTextEditIntegration()
    {
        $integrationId = 1;
        $integrationName = 'Test Name';

        $integrationData = [
            Integration::ID => $integrationId,
            Integration::NAME => $integrationName,
        ];

        /** @var \Magento\Framework\Registry $registry */
        $registry = Bootstrap::getObjectManager()
            ->get('Magento\Framework\Registry');
        $registry->register('current_integration', $integrationData);

        $headerText = $this->editBlock->getHeaderText();
        $this->assertEquals("Edit Integration '%1'", $headerText->getText());
        $this->assertEquals($integrationName, $headerText->getArguments()[0]);
    }

    public function testGetFormActionUrl()
    {
        $baseUrl = Bootstrap::getObjectManager()->get('Magento\Framework\Url')->getBaseUrl();
        $this->assertContains($baseUrl, $this->editBlock->getFormActionUrl());

    }
}
