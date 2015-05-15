<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Controller\Adminhtml\Bookmark;

use Magento\Authorization\Model\UserContextInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Api\BookmarkManagementInterface;
use Magento\Ui\Api\BookmarkRepositoryInterface;
use Magento\Ui\Api\Data\BookmarkInterface;
use Magento\Ui\Controller\Adminhtml\AbstractAction;

/**
 * Class Save action
 */
class Save extends AbstractAction
{
    /**
     * Identifier for current bookmark
     */
    const CURRENT_IDENTIFIER = 'current';

    /**
     * @var BookmarkRepositoryInterface
     */
    protected $bookmarkRepository;

    /**
     * @var BookmarkManagementInterface
     */
    protected $bookmarkManagement;

    /**
     * @var \Magento\Ui\Api\Data\BookmarkInterfaceFactory
     */
    protected $bookmarkFactory;

    /**
     * @var UserContextInterface
     */
    protected $userContext;

    /**
     * @param Context $context
     * @param UiComponentFactory $factory
     * @param BookmarkRepositoryInterface $bookmarkRepository
     * @param BookmarkManagementInterface $bookmarkManagement
     * @param \Magento\Ui\Api\Data\BookmarkInterfaceFactory $bookmarkFactory
     * @param UserContextInterface $userContext
     */
    public function __construct(
        Context $context,
        UiComponentFactory $factory,
        BookmarkRepositoryInterface $bookmarkRepository,
        BookmarkManagementInterface $bookmarkManagement,
        \Magento\Ui\Api\Data\BookmarkInterfaceFactory $bookmarkFactory,
        UserContextInterface $userContext
    ) {
        parent::__construct($context, $factory);
        $this->bookmarkRepository = $bookmarkRepository;
        $this->bookmarkManagement = $bookmarkManagement;
        $this->bookmarkFactory = $bookmarkFactory;
        $this->userContext = $userContext;
    }

    /**
     * Action for AJAX request
     *
     * @return void
     */
    public function execute()
    {
        $bookmark = $this->bookmarkFactory->create();
        $data = $this->_request->getParam('data');
        //Creation case
        if (isset($data['views'])) {
            foreach ($data['views'] as $identifier => $data) {
                $this->updateBookmark(
                    $bookmark,
                    $identifier,
                    (isset($data['label']) ? $data['label'] : ''),
                    (isset($data['data']) ? $data['data'] : '')
                );
            }
        } else {
            //Update case
            $identifier = isset($data['activeIndex']) ? $data['activeIndex'] : (isset($data[self::CURRENT_IDENTIFIER]) ? self::CURRENT_IDENTIFIER : '');
            $updateBookmark = $this->bookmarkManagement->getByIdentifierNamespace(
                $identifier,
                $this->_request->getParam('namespace')
            );

            if ($updateBookmark) {
                $updateBookmark->setCurrent(true);
                $this->updateBookmark($updateBookmark, $identifier, '', $data[$identifier]);
            }
        }
    }

    /**
     * Update bookmarks based on request params
     *
     * @param BookmarkInterface $bookmark
     * @param $identifier
     * @param $title
     * @param array $config
     */
    protected function updateBookmark(BookmarkInterface $bookmark, $identifier, $title, array $config = []) {
        $bookmark->setUserId($this->userContext->getUserId())
            ->setNamespace($this->_request->getParam('namespace'))
            ->setIdentifier($identifier)
            ->setTitle($title)
            ->setConfig($config);

        $this->bookmarkRepository->save($bookmark);
    }
}
