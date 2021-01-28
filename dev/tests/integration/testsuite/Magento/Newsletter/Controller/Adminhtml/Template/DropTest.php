<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Newsletter\Controller\Adminhtml\Template;

use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\Data\Form\FormKey;
use Magento\Newsletter\Model\Template;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\TestCase\AbstractBackendController;

class DropTest extends AbstractBackendController
{
    public function testDefaultTemplateAction()
    {
        $website = $this->_objectManager
            ->get(StoreManagerInterface::class)
            ->getWebsite();

        $storeId = $website->getDefaultStore()
            ->getId();

        /** @var $formKey FormKey */
        $formKey = $this->_objectManager->get(FormKey::class);
        $post = [
            'form_key' => $formKey->getFormKey(),
            'type' => Template::TYPE_HTML,
            'preview_store_id' => $storeId,
            'text' => 'Template {{var this.template_id}}:{{var this.getData(template_id)}} Text'
        ];
        $this->getRequest()->setPostValue($post);
        $this->getRequest()->setMethod(HttpRequest::METHOD_POST);
        $this->dispatch('backend/newsletter/template/drop');
        $this->assertStringContainsString(
            'Template 123: Text',
            $this->getResponse()->getBody()
        );
    }
}
