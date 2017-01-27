<?php
/**
 * Copyright © 2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Magento\Analytics\Controller\Adminhtml\Export;

use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Analytics\Model\Export;
use Magento\Framework\App\Filesystem\DirectoryList;

/**
 * Class Example
 */
class Example extends Action
{
    /**
     * @var Export
     */
    private $export;

    /**
     * @var FileFactory
     */
    private $fileFactory;

    /**
     * Example constructor.
     *
     * @param Action\Context $context
     * @param Export $export
     * @param FileFactory $fileFactory
     */
    public function __construct(
        Action\Context $context,
        Export $export,
        FileFactory $fileFactory
    ) {
        parent::__construct($context);
        $this->export = $export;
        $this->fileFactory = $fileFactory;
    }

    /**
     * Controller for demo
     *
     * @return ResponseInterface
     * @throws \Exception
     */
    public function execute()
    {
        return $this->fileFactory->create(
            'analytics-export.tgz',
            $this->export->getArchiveContent(),
            DirectoryList::VAR_DIR
        );
    }
}
