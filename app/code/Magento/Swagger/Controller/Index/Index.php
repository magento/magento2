<?php
/***
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Swagger\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\View\Page\Config as PageConfig;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Magento\Swagger\Model\Config;

/**
 * Renders Swagger
 */
class Index extends Action implements HttpGetActionInterface
{
    /**
     * @var PageConfig
     */
    private $pageConfig;

    /**
     * @var PageFactory
     */
    private $pageFactory;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param Context $context
     * @param PageConfig $pageConfig
     * @param PageFactory $pageFactory
     * @param Config|null $config
     */
    public function __construct(
        Context $context,
        PageConfig $pageConfig,
        PageFactory $pageFactory,
        ?Config $config = null
    ) {
        parent::__construct($context);
        $this->pageConfig = $pageConfig;
        $this->pageFactory = $pageFactory;
        $this->config = $config ?? ObjectManager::getInstance()
                ->get(Config::class);
    }

    /**
     * @inheritDoc
     *
     * @return Page
     */
    public function execute()
    {
        $this->pageConfig->addBodyClass('swagger-section');
        return $this->pageFactory->create();
    }

    /**
     * @inheritDoc
     */
    public function dispatch(RequestInterface $request)
    {
        if (!$this->config->isEnabled()) {
            throw new NotFoundException(__('Page not found.'));
        }

        return parent::dispatch($request);
    }
}
