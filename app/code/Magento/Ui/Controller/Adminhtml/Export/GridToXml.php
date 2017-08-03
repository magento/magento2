<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Controller\Adminhtml\Export;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Model\Export\ConvertToXml;
use Magento\Framework\App\Response\Http\FileFactory;

/**
 * Class Render
 * @since 2.0.0
 */
class GridToXml extends Action
{
    /**
     * @var ConvertToXml
     * @since 2.0.0
     */
    protected $converter;

    /**
     * @var FileFactory
     * @since 2.0.0
     */
    protected $fileFactory;

    /**
     * @param Context $context
     * @param ConvertToXml $converter
     * @param FileFactory $fileFactory
     * @since 2.0.0
     */
    public function __construct(
        Context $context,
        ConvertToXml $converter,
        FileFactory $fileFactory
    ) {
        parent::__construct($context);
        $this->converter = $converter;
        $this->fileFactory = $fileFactory;
    }

    /**
     * Export data provider to XML
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     * @return \Magento\Framework\App\ResponseInterface
     * @since 2.0.0
     */
    public function execute()
    {
        return $this->fileFactory->create('export.xml', $this->converter->getXmlFile(), 'var');
    }
}
