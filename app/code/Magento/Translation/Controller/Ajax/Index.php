<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Translation\Controller\Ajax;

use Exception;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Translate\Inline\ParserInterface;

class Index extends Action
{
    /**
     * @param Context $context
     * @param ParserInterface $inlineParser
     */
    public function __construct(
        Context $context,
        protected readonly ParserInterface $inlineParser
    ) {
        parent::__construct($context);
    }

    /**
     * Ajax action for inline translation
     *
     * @return void
     */
    public function execute()
    {
        $translate = (array)$this->getRequest()->getPost('translate');

        try {
            $response = $this->inlineParser->processAjaxPost($translate);
        } catch (Exception $e) {
            $response = "{error:true,message:'" . $e->getMessage() . "'}";
        }
        $this->getResponse()->representJson(json_encode($response));
        $this->_actionFlag->set('', self::FLAG_NO_POST_DISPATCH, true);
    }
}
