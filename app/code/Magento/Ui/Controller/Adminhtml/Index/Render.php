<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Controller\Adminhtml\Index;

use Magento\Backend\App\Action\Context;
use Magento\Ui\Controller\Adminhtml\AbstractAction;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponentInterface;

/**
 * Class Render
 */
class Render extends AbstractAction
{
    /**
     * @var \Magento\Ui\Model\BookmarkFactory
     */
    protected $bookmarkFactory;

    /**
     * Constructor
     *
     * @param Context $context
     * @param UiComponentFactory $factory
     * @param \Magento\Ui\Model\BookmarkFactory $bookmarkFactory
     */
    public function __construct(
        Context $context,
        UiComponentFactory $factory,
        \Magento\Ui\Model\BookmarkFactory $bookmarkFactory
    ) {
        parent::__construct($context, $factory);
        $this->bookmarkFactory = $bookmarkFactory;
    }

    /**
     * Action for AJAX request
     *
     * @return void
     */
    public function execute()
    {
        $bookmark = $this->bookmarkFactory->create();
        /** @var $bookmark \Magento\Ui\Model\Bookmark */
        $bookmark->saveState($this->_request->getParams());
        $component = $this->factory->create($this->_request->getParam('namespace'));
        $this->prepareComponent($component);
        $this->_response->appendBody((string) $component->render());
    }

    /**
     * Call prepare method in the component UI
     *
     * @param UiComponentInterface $component
     * @return void
     */
    protected function prepareComponent(UiComponentInterface $component)
    {
        $childComponents = $component->getChildComponents();
        if (!empty($childComponents)) {
            foreach ($childComponents as $child) {
                $this->prepareComponent($child);
            }
        }
        $component->prepare();
    }
}
