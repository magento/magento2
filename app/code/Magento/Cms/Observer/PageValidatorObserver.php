<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\Cms\Observer;

use Magento\Cms\Api\Data\PageInterface;
use Magento\Cms\Controller\Page\SaveManager;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Performing additional validation each time a user saves a CMS page.
 */
class PageValidatorObserver implements ObserverInterface
{
    /**
     * @var SaveManager
     */
    private $saveManager;

    /**
     * @param SaveManager $saveManager
     */
    public function __construct(SaveManager $saveManager)
    {
        $this->saveManager = $saveManager;
    }

    /**
     * @inheritDoc
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        /** @var PageInterface $page */
        $page = $observer->getEvent()->getData('page');
        $this->saveManager->validatePage($page);
    }
}
