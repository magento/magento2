<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Variable\Controller\Adminhtml\System\Variable;

/**
 * Display Variables edit form page
 *
 * @api
 * @since 100.0.2
 */
class Edit extends \Magento\Variable\Controller\Adminhtml\System\Variable
{
    /**
     * Edit Action
     *
     * @return \Magento\Backend\Model\View\Result\Page
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
            \Magento\Variable\Block\System\Variable\Edit::class
        ))->addJs(
            $resultPage->getLayout()->createBlock(
                \Magento\Framework\View\Element\Template::class,
                '',
                ['data' => ['template' => 'Magento_Variable::system/variable/js.phtml']]
            )
        );
        return $resultPage;
    }
}
