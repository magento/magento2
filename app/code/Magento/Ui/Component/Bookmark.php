<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Ui\Component;

use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Api\BookmarkManagementInterface;
use Magento\Ui\Api\BookmarkRepositoryInterface;

/**
 * Class Bookmark
 */
class Bookmark extends AbstractComponent
{
    const NAME = 'bookmark';

    /**
     * @var BookmarkRepositoryInterface
     */
    protected $bookmarkRepository;

    /**
     * @var FilterBuilder
     */
    protected $filterBuilder;

    /**
     * @var BookmarkManagementInterface
     */
    protected $bookmarkManagement;

    /**
     * @param ContextInterface $context
     * @param BookmarkRepositoryInterface $bookmarkRepository
     * @param BookmarkManagementInterface $bookmarkManagement
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        BookmarkRepositoryInterface $bookmarkRepository,
        BookmarkManagementInterface $bookmarkManagement,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $components, $data);
        $this->bookmarkRepository = $bookmarkRepository;
        $this->bookmarkManagement = $bookmarkManagement;
    }


    /**
     * Get component name
     *
     * @return string
     */
    public function getComponentName()
    {
        return static::NAME;
    }

    /**
     * Register component
     *
     * @return void
     */
    public function prepare()
    {
        /** TODO: Remove Repository and Management dependencies */
        $namespace = $this->getContext()->getRequestParam('namespace');
        $config = [];
        if (!empty($namespace)) {
            $bookmarks = $this->bookmarkManagement->loadByNamespace($namespace);
            foreach ($bookmarks->getItems() as $bookmark) {
                $config[] = $bookmark->getConfig();
            }

        }

        //var_dump($namespace);
//        $list = $this->bookmarkManagement->loadByNamespace($namespace);
//        foreach ($list->getItems() as $item) {
//            var_dump($item->getConfig());
//        }
//        dd();
        parent::prepare();

        $jsConfig = $this->getConfiguration($this);
        $this->getContext()->addComponentDefinition($this->getComponentName(), $jsConfig);
    }
}
