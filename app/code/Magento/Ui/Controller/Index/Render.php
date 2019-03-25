<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Controller\Index;

use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Response\Http as HttpResponse;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponentInterface;
use Magento\Ui\Model\UiComponentTypeResolver;

/**
 * Is responsible for providing ui components information on store front.
 *
 * @SuppressWarnings(PHPMD.AllPurposeAction)
 */
class Render extends \Magento\Framework\App\Action\Action
{
    /**
     * @var Context
     */
    private $context;

    /**
     * @var UiComponentFactory
     */
    private $uiComponentFactory;

    /**
     * @var UiComponentTypeResolver
     */
    private $contentTypeResolver;

    /**
     * Render constructor.
     * @param Context $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UiComponentTypeResolver|null $contentTypeResolver
     */
    public function __construct(
        Context $context,
        UiComponentFactory $uiComponentFactory,
        UiComponentTypeResolver $contentTypeResolver = null
    ) {
        parent::__construct($context);
        $this->context = $context;
        $this->uiComponentFactory = $uiComponentFactory;
        $this->contentTypeResolver = $contentTypeResolver
            ?? ObjectManager::getInstance()->get(UiComponentTypeResolver::class);
    }

    /**
     * Action for AJAX request
     *
     * @return void
     */
    public function execute()
    {
        if ($this->_request->getParam('namespace') === null) {
            $this->_redirect('noroute');
            return;
        }

        $component = $this->uiComponentFactory->create($this->_request->getParam('namespace'));
        $this->prepareComponent($component);
        /** @var HttpResponse $response */
        $response = $this->getResponse();
        $response->appendBody((string) $component->render());
        $response->setHeader('Content-Type', $this->contentTypeResolver->resolve($component->getContext()), true);
    }

    /**
     * Call prepare method in the component UI
     *
     * @param UiComponentInterface $component
     * @return void
     */
    private function prepareComponent(UiComponentInterface $component)
    {
        foreach ($component->getChildComponents() as $child) {
            $this->prepareComponent($child);
        }
        $component->prepare();
    }
}
