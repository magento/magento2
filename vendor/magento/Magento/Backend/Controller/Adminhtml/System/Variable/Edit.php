<?php
/**
 *
 * @copyright Copyright (c) 2014 X.commerce, Inc. (http://www.magentocommerce.com)
 */
namespace Magento\Backend\Controller\Adminhtml\System\Variable;

class Edit extends \Magento\Backend\Controller\Adminhtml\System\Variable
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
        $resultPage->addContent($resultPage->getLayout()->createBlock('Magento\Backend\Block\System\Variable\Edit'))
            ->addJs(
                $resultPage->getLayout()->createBlock(
                    'Magento\Framework\View\Element\Template',
                    '',
                    ['data' => ['template' => 'Magento_Backend::system/variable/js.phtml']]
                )
            );
        return $resultPage;
    }
}
