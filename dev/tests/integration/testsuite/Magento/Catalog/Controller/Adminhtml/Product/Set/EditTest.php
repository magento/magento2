<?php
/**
 * Copyright 2025 Adobe
 * All Rights Reserved.
 */

declare(strict_types=1);

namespace Magento\Catalog\Controller\Adminhtml\Product\Set;

use Magento\Framework\Message\MessageInterface;
use Magento\TestFramework\Eav\Model\GetAttributeSetByName;
use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * Test edit attribute set controller.
 *
 * @magentoAppArea adminhtml
 */
class EditTest extends AbstractBackendController
{
    /**
     * @var GetAttributeSetByName
     */
    private $getAttributeSetByName;

    /**
     * @inheritdoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->getAttributeSetByName = $this->_objectManager->get(GetAttributeSetByName::class);
    }

    /**
     * Test edit page loads successfully with valid attribute set ID.
     *
     * @magentoDataFixture Magento/Catalog/_files/attribute_set_based_on_default.php
     *
     * @magentoDbIsolation disabled
     *
     * @return void
     */
    public function testEditWithValidAttributeSetId(): void
    {
        $attributeSet = $this->getAttributeSetByName->execute('new_attribute_set');
        $this->assertNotNull($attributeSet);

        $attributeSetId = (int)$attributeSet->getAttributeSetId();

        // Suppress deprecation warnings from AdminNotification Feed (pre-existing issue)
        $errorReporting = error_reporting();
        error_reporting($errorReporting & ~E_DEPRECATED);

        $this->dispatch('backend/catalog/product_set/edit/id/' . $attributeSetId);

        error_reporting($errorReporting);

        $this->assertEquals(200, $this->getResponse()->getHttpResponseCode());
        $body = $this->getResponse()->getBody();

        // Verify page title contains attribute set name
        $this->assertStringContainsString((string)$attributeSet->getAttributeSetName(), $body);
        
        // Verify page content loaded (check for the actual form ID)
        $this->assertStringContainsString('set-prop-form', $body);
    }

    /**
     * Test edit with non-existing attribute set ID shows error and redirects.
     *
     * @return void
     */
    public function testEditWithNonExistingAttributeSetId(): void
    {
        $nonExistingId = 999999;

        // Suppress deprecation warnings from AdminNotification Feed (pre-existing issue)
        $errorReporting = error_reporting();
        error_reporting($errorReporting & ~E_DEPRECATED);

        $this->dispatch('backend/catalog/product_set/edit/id/' . $nonExistingId);

        error_reporting($errorReporting);

        // Should redirect to index page
        $this->assertRedirect($this->stringContains('catalog/product_set/index'));

        // Should show error message with the non-existing ID
        $this->assertSessionMessages(
            $this->equalTo([(string)__('Attribute set %1 does not exist.', $nonExistingId)]),
            MessageInterface::TYPE_ERROR
        );
    }

    /**
     * Test edit without ID parameter shows error and redirects.
     *
     * @return void
     */
    public function testEditWithMissingIdParameter(): void
    {
        // Suppress deprecation warnings from AdminNotification Feed (pre-existing issue)
        $errorReporting = error_reporting();
        error_reporting($errorReporting & ~E_DEPRECATED);

        // Dispatch without /id/ parameter
        $this->dispatch('backend/catalog/product_set/edit');

        error_reporting($errorReporting);

        // Should redirect to index page
        $this->assertRedirect($this->stringContains('catalog/product_set/index'));

        // Should show error message (with empty value for %1 placeholder when ID is null)
        $this->assertSessionMessages(
            $this->equalTo([(string)__('Attribute set %1 does not exist.', null)]),
            MessageInterface::TYPE_ERROR
        );
    }
}
