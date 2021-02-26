<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Cms\Model\Page\CustomLayout;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Cms\Model\PageRepository;

/**
 * Class for layout update validation
 */
class CustomLayoutValidator
{
    /**
     * @var PageFactory
     */
    private $pageFactory;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    /**
     * CustomLayoutValidator constructor.
     * @param PageRepository $pageFactory
     * @param ManagerInterface $messageManager
     */
    public function __construct(
        PageRepository $pageFactory,
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
    public function validate(array $data) : bool
    {
        $layoutUpdate = isset($data['layout_update_xml']) ? $data['layout_update_xml'] : null;
        $customLayoutUpdate = isset($data['custom_layout_update_xml']) ? $data['custom_layout_update_xml'] : null;
        if (isset($data['page_id'])) {
            $page = $this->pageFactory->getById($data['page_id']);
            $oldLayoutUpdate = $page->getId() ? $page->getLayoutUpdateXml() : null;
            $oldCustomLayoutUpdate = $page->getId() ? $page->getCustomLayoutUpdateXml() : null;
            if ($layoutUpdate && $oldLayoutUpdate !== $layoutUpdate
                || $customLayoutUpdate && $oldCustomLayoutUpdate !== $customLayoutUpdate
            ) {
                throw new LocalizedException(__('Custom layout update text cannot be changed, only removed'));
            }
        } else {
            if ($layoutUpdate || $customLayoutUpdate) {
                throw new LocalizedException(__('Custom layout update text cannot be changed, only removed'));
            }
        }
        return true;
    }
}
