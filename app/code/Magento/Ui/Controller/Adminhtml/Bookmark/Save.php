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
    protected function execute()
    {
        $bookmark = $this->bookmarkFactory->create();
        $data = $this->_request->getParam('data');
        if (isset($data['views'])) {
            foreach ($data['views'] as $identifier => $data) {
                $updateBookmark = $this->checkBookmark($identifier);
                if ($updateBookmark !== false) {
                    $bookmark = $updateBookmark;
                }

                $this->updateBookmark(
                    $bookmark,
                    $identifier,
                    (isset($data['label']) ? $data['label'] : ''),
                    $data
                );
            }
        } else {
            $identifier = isset($data['activeIndex'])
                ? $data['activeIndex']
                : (isset($data[self::CURRENT_IDENTIFIER]) ? self::CURRENT_IDENTIFIER : '');
            $updateBookmark = $this->checkBookmark($identifier);
            if ($updateBookmark !== false) {
                $bookmark = $updateBookmark;
            }

            $this->updateBookmark($bookmark, $identifier, '', $data[$identifier]);
        }
    }

    /**
     * Update bookmarks based on request params
     *
     * @param BookmarkInterface $bookmark
     * @param string $identifier
     * @param string $title
     * @param array $config
     * @return void
     */
    protected function updateBookmark(BookmarkInterface $bookmark, $identifier, $title, array $config = [])
    {
        $this->filterVars($config);
        $bookmark->setUserId($this->userContext->getUserId())
            ->setNamespace($this->_request->getParam('namespace'))
            ->setIdentifier($identifier)
            ->setTitle($title)
            ->setConfig($config)
            ->setCurrent($identifier !== self::CURRENT_IDENTIFIER);
        $this->bookmarkRepository->save($bookmark);

        $bookmarks = $this->bookmarkManagement->loadByNamespace($this->_request->getParam('namespace'));
        foreach ($bookmarks->getItems() as $bookmark) {
            if ($bookmark->getIdentifier() == $identifier) {
                continue;
            }
            $bookmark->setCurrent(false);
            $this->bookmarkRepository->save($bookmark);
        }
    }

    /**
     * Check bookmark by identifier
     *
     * @param string $identifier
     * @return bool|BookmarkInterface
     */
    protected function checkBookmark($identifier)
    {
        $result = false;

        $updateBookmark = $this->bookmarkManagement->getByIdentifierNamespace(
            $identifier,
            $this->_request->getParam('namespace')
        );

        if ($updateBookmark) {
            $result = $updateBookmark;
        }

        return $result;
    }

    /**
     * Filter boolean vars
     *
     * @param array $data
     * @return void
     */
    protected function filterVars(array & $data = [])
    {
        foreach ($data as & $value) {
            if (is_array($value)) {
                $this->filterVars($value);
            } else {
                if ($value == 'true') {
                    $value = true;
                } elseif ($value == 'false') {
                    $value = false;
                } elseif (is_numeric($value)) {
                    $value = (int) $value;
                }
            }
        }
    }
}
