<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Element\UiComponent\ContentType\ContentTypeFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\Listing\Columns;

/**
 * @api
 * @since 100.0.2
 */
class Listing extends AbstractComponent
{
    public const NAME = 'listing';

    /**
     * @var array
     */
    protected $columns = [];

    /**
     * @var ContentTypeFactory
     */
    private ContentTypeFactory $contentTypeFactory;

    /**
     * @param ContextInterface $context
     * @param array $components
     * @param array $data
     * @param ContentTypeFactory|null $contentTypeFactory
     */
    public function __construct(
        ContextInterface $context,
        array $components = [],
        array $data = [],
        ?ContentTypeFactory $contentTypeFactory = null
    ) {
        $this->contentTypeFactory = $contentTypeFactory ?: ObjectManager::getInstance()->get(ContentTypeFactory::class);
        parent::__construct($context, $components, $data);
    }

    /**
     * Get component name
     *
     * @return string
     */
    public function getComponentName()
    {
        return static::NAME;
    }

    /**
     * @inheritdoc
     */
    public function getDataSourceData()
    {
        return ['data' => $this->getContext()->getDataProvider()->getData()];
    }

    /**
     * Render content depending on specified type
     *
     * @param string $contentType
     * @return string
     */
    public function render(string $contentType = '')
    {
        if ($contentType) {
            return $this->contentTypeFactory->get($contentType)->render($this, $this->getTemplate());
        }

        return parent::render();
    }
}
