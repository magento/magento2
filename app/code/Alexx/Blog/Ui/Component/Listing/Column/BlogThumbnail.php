<?php

namespace Alexx\Blog\Ui\Component\Listing\Column;

use Alexx\Blog\Model\PictureConfig;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\App\Action\Context as ActionContext;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\DataObject;

/**
 * BlogThumbnail column
 */
class BlogThumbnail extends Column
{
    const NAME = 'thumbnail';

    private $_objectManager;

    /**
     * Constructor
     *
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param ActionContext $actionContext
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        ActionContext $actionContext,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->_objectManager = $actionContext->getObjectManager();
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        $pictureConfig=$this->_objectManager->get(PictureConfig::class);
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                $model = new DataObject($item);
                $item[$fieldName . '_src'] = $pictureConfig->getBlogImageUrl($model->getPicture());
                $item[$fieldName . '_alt'] = $model->getTheme();
            }
        }
        return $dataSource;
    }
}
