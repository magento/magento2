<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\ImportExport\Ui\Component\Columns;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\ImportExport\Model\LocalizedFileName;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * File display name column
 */
class FileDisplayName extends Column
{
    /**
     * @var LocalizedFileName
     */
    private $localizedFileName;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param LocalizedFileName $localizedFileName
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        LocalizedFileName $localizedFileName,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->localizedFileName = $localizedFileName;
    }

    /**
     * @inheritdoc
     */
    public function prepareDataSource(array $dataSource)
    {
        $fieldName = $this->getData('name');
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as & $item) {
                $item[$fieldName] = $this->localizedFileName->getFileDisplayName($item['file_name']);
            }
        }

        return $dataSource;
    }
}
