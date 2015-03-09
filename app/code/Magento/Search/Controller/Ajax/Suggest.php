<?php
/**
 *
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Search\Controller\Ajax;

use Magento\Search\Model\AutocompleteInterface;
use Magento\Framework\App\Action\Context;

class Suggest extends \Magento\Framework\App\Action\Action
{
    /**
     * @var  AutocompleteInterface
     */
    private $autocomplete;

    /**
     * @param Context $context
     * @param AutocompleteInterface $autocomplete
     */
    public function __construct(
        Context $context,
        AutocompleteInterface $autocomplete
    ) {
        parent::__construct($context);
        $this->autocomplete = $autocomplete;
    }

    /**
     * @return void
     */
    public function execute()
    {
        if (!$this->getRequest()->getParam('q', false)) {
            $this->getResponse()->setRedirect($this->_url->getBaseUrl());
            return;
        }

        $autocompleteData = $this->autocomplete->getItems();
        $responseData = [];
        foreach ($autocompleteData as $resultItem) {
            $responseData[] = $resultItem->toArray();
        }
        $this->getResponse()->representJson(json_encode($responseData));
    }
}
