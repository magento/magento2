<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Controller\Adminhtml\Bookmark;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Controller\Adminhtml\AbstractAction;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Ui\Api\BookmarkRepositoryInterface;

/**
 * Class Delete action
 */
class Delete extends AbstractAction
{
    /**
     * @var BookmarkRepositoryInterface
     */
    protected $bookmarkRepository;

    /**
     * @param Context $context
     * @param UiComponentFactory $factory
     * @param BookmarkRepositoryInterface $bookmarkRepository
     */
    public function __construct(
        Context $context,
        UiComponentFactory $factory,
        BookmarkRepositoryInterface $bookmarkRepository
    ) {
        parent::__construct($context, $factory);
        $this->bookmarkRepository = $bookmarkRepository;
    }

    /**
     * Action for AJAX request
     *
     * @return void
     */
    public function execute()
    {
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
        foreach ($component->getChildComponents() as $child) {
            $this->prepareComponent($child);
        }

        $component->prepare();
    }
}
