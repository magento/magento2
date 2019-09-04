<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Controller\Adminhtml\Promo\Quote;

use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * New action html test
 */
class NewActionHtmlTest extends AbstractBackendController
{
    /**
     * Test verifies that execute method has the proper data-form-part value in html response
     *
     * @return void
     */
    public function testExecute(): void
    {
        $formName = 'test_form';
        $this->getRequest()->setParams(
            [
                'id' => 1,
                'form_namespace' => $formName,
                'type' => 'Magento\SalesRule\Model\Rule\Condition\Product|quote_item_price',
            ]
        );
        $objectManager = Bootstrap::getObjectManager();
        /** @var NewActionHtml $controller */
        $controller = $objectManager->create(NewActionHtml::class);
        $controller->execute();
        $html = $this->getResponse()
            ->getBody();
        $this->assertContains($formName, $html);
    }
}
