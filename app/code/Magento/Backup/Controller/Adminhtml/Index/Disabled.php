<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Backup\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
<<<<<<< HEAD
=======
use Magento\Framework\App\Action\HttpGetActionInterface;
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
use Magento\Framework\View\Result\PageFactory;

/**
 * Inform that backup is disabled.
 */
<<<<<<< HEAD
class Disabled extends Action
=======
class Disabled extends Action implements HttpGetActionInterface
>>>>>>> 35c4f041925843d91a58c1d4eec651f3013118d3
{
    /**
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Backend::backup';

    /**
     * @var PageFactory
     */
    private $pageFactory;

    /**
     * @param Context $context
     * @param PageFactory $pageFactory
     */
    public function __construct(Context $context, PageFactory $pageFactory)
    {
        parent::__construct($context);
        $this->pageFactory = $pageFactory;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        return $this->pageFactory->create();
    }
}
