<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Controller\Adminhtml\Export;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Model\Export\ConvertToCsv;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\ObjectManager;
use Magento\Ui\Component\MassAction\Filter;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Class Render
 */
class GridToCsv extends Action
{
    /**
     * @param Context $context
     * @param ConvertToCsv $converter
     * @param FileFactory $fileFactory
     * @param Filter|null $filter
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        Context $context,
        protected readonly ConvertToCsv $converter,
        protected readonly FileFactory $fileFactory,
        private ?Filter $filter = null,
        private ?LoggerInterface $logger = null
    ) {
        parent::__construct($context);
        $this->filter = $filter ?: ObjectManager::getInstance()->get(Filter::class);
        $this->logger = $logger ?: ObjectManager::getInstance()->get(LoggerInterface::class);
    }

    /**
     * Export data provider to CSV
     *
     * @throws LocalizedException
     * @return ResponseInterface
     */
    public function execute()
    {
        return $this->fileFactory->create('export.csv', $this->converter->getCsvFile(), 'var');
    }

    /**
     * Checking if the user has access to requested component.
     *
     * @inheritDoc
     */
    protected function _isAllowed()
    {
        if ($this->_request->getParam('namespace')) {
            try {
                $component = $this->filter->getComponent();
                $dataProviderConfig = $component->getContext()
                    ->getDataProvider()
                    ->getConfigData();
                if (isset($dataProviderConfig['aclResource'])) {
                    return $this->_authorization->isAllowed(
                        $dataProviderConfig['aclResource']
                    );
                }
            } catch (Throwable $exception) {
                $this->logger->critical($exception);

                return false;
            }
        }

        return true;
    }
}
