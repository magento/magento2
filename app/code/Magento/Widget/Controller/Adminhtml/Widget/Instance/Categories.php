<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Widget\Controller\Adminhtml\Widget\Instance;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Raw;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Math\Random;
use Magento\Framework\Registry;
use Magento\Framework\Translate\InlineInterface;
use Magento\Framework\View\Layout;
use Magento\Widget\Block\Adminhtml\Widget\Catalog\Category\Chooser;
use Magento\Widget\Controller\Adminhtml\Widget\Instance;
use Magento\Widget\Model\Widget\InstanceFactory;
use Psr\Log\LoggerInterface;

class Categories extends Instance
{
    /**
     * @param Context $context
     * @param Registry $coreRegistry
     * @param InstanceFactory $widgetFactory
     * @param LoggerInterface $logger
     * @param Random $mathRandom
     * @param InlineInterface $translateInline
     * @param Layout $layout
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        InstanceFactory $widgetFactory,
        LoggerInterface $logger,
        Random $mathRandom,
        InlineInterface $translateInline,
        protected readonly Layout $layout
    ) {
        parent::__construct($context, $coreRegistry, $widgetFactory, $logger, $mathRandom, $translateInline);
    }

    /**
     * Categories chooser Action (Ajax request)
     *
     * @return Raw
     */
    public function execute()
    {
        $selected = $this->getRequest()->getParam('selected', '');
        $isAnchorOnly = $this->getRequest()->getParam('is_anchor_only', 0);

        /** @var Chooser $chooser */
        $chooser = $this->layout->createBlock(Chooser::class)
            ->setUseMassaction(true)
            ->setId($this->mathRandom->getUniqueHash('categories'))
            ->setIsAnchorOnly($isAnchorOnly)
            ->setSelectedCategories(explode(',', $selected));

        /** @var Raw $resultRaw */
        $resultRaw = $this->resultFactory->create(ResultFactory::TYPE_RAW);
        return $resultRaw->setContents($chooser->toHtml());
    }
}
