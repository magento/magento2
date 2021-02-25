<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Cms\Model\Page\CustomLayout;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Cms\Model\PageFactory;

class CustomLayoutValidator
{
    /**
     * @var PageFactory
     */
    protected $pageFactory;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * CustomLayoutValidator constructor.
     * @param PageFactory $pageFactory
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        PageFactory $pageFactory,
        ManagerInterface $messageManager
    ) {
        $this->pageFactory = $pageFactory;
        $this->messageManager = $messageManager;
    }

    /**
     * Validates layout update and custom layout update for CMS page
     *
     * @param array $data
     * @return bool
     * @throws LocalizedException
     */
    public function validate(array $data)
    {
        $layoutUpdate = isset($data['layout_update_xml']) ? $data['layout_update_xml'] : null;
        $customLayoutUpdate = isset($data['custom_layout_update_xml']) ? $data['custom_layout_update_xml'] : null;
        $page = $this->pageFactory->create();
        $page->load($data['page_id']);
        $oldLayoutUpdate = $page->getId() ? $page->getLayoutUpdateXml() : null;
        $olCustomLayoutUpdate = $page->getId() ? $page->getCustomLayoutUpdateXml() : null;
        if ($layoutUpdate && $oldLayoutUpdate !== $layoutUpdate
            || $customLayoutUpdate && $olCustomLayoutUpdate !== $customLayoutUpdate
        ) {
            throw new LocalizedException(__('Custom layout update text cannot be changed, only removed'));
        }
        return true;
    }
}
