<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Variable\Controller\Adminhtml\System\Variable;

use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\View\Element\Template;
use Magento\Variable\Block\System\Variable\Edit as SystemVariableEdit;
use Magento\Variable\Controller\Adminhtml\System\Variable;
use Magento\Framework\App\Action\HttpGetActionInterface;

/**
 * Display Variables edit form page
 *
 * @api
 * @since 100.0.2
 */
class Edit extends Variable implements HttpGetActionInterface
{
    /**
     * Edit Action
     *
     * @return Page
     */
    public function execute()
    {
        $variable = $this->_initVariable();

        $resultPage = $this->createPage();
        $resultPage->getConfig()->getTitle()->prepend(__('Custom Variables'));
        $resultPage->getConfig()->getTitle()->prepend(
            $variable->getId() ? $variable->getCode() : __('New Custom Variable')
        );
        $resultPage->addContent($resultPage->getLayout()->createBlock(
            SystemVariableEdit::class
        ))->addJs(
            $resultPage->getLayout()->createBlock(
                Template::class,
                '',
                ['data' => ['template' => 'Magento_Variable::system/variable/js.phtml']]
            )
        );
        return $resultPage;
    }
}
