<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace CKEditor\CKEditor4\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Module\Status;

class SaveConfigObserver implements ObserverInterface
{
    /**
     * @var Status
     */
    private $moduleStatus;

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleManager;

    /**
     * UpgradeData constructor
     *
     * @param Status $moduleStatus
     */
    public function __construct(
        Status $moduleStatus,
        ModuleDataSetupInterface $moduleManager
    ) {
        $this->moduleStatus = $moduleStatus;
        $this->moduleManager = $moduleManager;
    }

    /**
     * Switch out the Wysiwyg editors
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if ($observer->hasData('configData') && $observer->hasData('request')) {
            $configData = $observer->getData('configData');
            $request = $observer->getData('request');
            $groups = $request->getPost('groups');
            $enabled = $configData['section'] === 'cms'
                && $groups['wysiwyg']['fields']['editor']['value'] === 'ckeditor';
            $this->toggleEditor($enabled);
        }

        return $this;
    }

    /**
     * Turns the editor on and off
     *
     * @param bool $enabled
     */
    public function toggleEditor($enabled)
    {
        $this->moduleManager->startSetup();
        $this->moduleStatus->setIsEnabled($enabled, ['CKEditor_CKEditor4']);
        $this->moduleManager->endSetup();
    }
}

