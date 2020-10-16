<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\SalesRule\Controller\Adminhtml\Promo\Quote;

use Magento\TestFramework\TestCase\AbstractBackendController;

/**
 * New action html test
 *
 * Verify the request object contains the proper form object for action
 * @magentoAppArea adminhtml
 */
class NewActionHtmlTest extends AbstractBackendController
{
    /**
     * @var string
     */
    protected $resource = 'Magento_SalesRule::quote';

    /**
     * @var string
     */
    protected $uri = 'backend/sales_rule/promo_quote/newActionHtml';

    /**
     * @var string
     */
    private $formName = 'test_form';

    /**
     * @var string
     */
    private $requestFormName = 'rule_actions_fieldset_';

    /**
     * Test verifies that execute method has the proper data-form-part value in html response
     *
     * @return void
     */
    public function testExecute(): void
    {
        $this->prepareRequest();
        $this->dispatch($this->uri);
        $html = $this->getResponse()
            ->getBody();
        $this->assertStringContainsString($this->formName, $html);
    }

    /**
     * @inheritdoc
     */
    public function testAclHasAccess()
    {
        $this->prepareRequest();
        parent::testAclHasAccess();
    }

    /**
     * @inheritdoc
     */
    public function testAclNoAccess()
    {
        $this->prepareRequest();
        parent::testAclNoAccess();
    }

    /**
     * Prepare request
     *
     * @return void
     */
    private function prepareRequest(): void
    {
        $this->getRequest()->setParams(
            [
                'id' => 1,
                'form' => $this->requestFormName,
                'form_namespace' => $this->formName,
                'type' => 'Magento\SalesRule\Model\Rule\Condition\Product|quote_item_price',
            ]
        )->setMethod('POST');
    }
}
