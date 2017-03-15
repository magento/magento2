<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model\Config\Export;

use Magento\Config\App\Config\Source\DumpConfigSourceInterface;
use Magento\Config\Model\Config\TypePool;
use Magento\Config\Model\Placeholder\PlaceholderFactory;
use Magento\Config\Model\Placeholder\PlaceholderInterface;
use Magento\Framework\App\Config\CommentInterface;
use Magento\Framework\App\ObjectManager;

/**
 * Class Comment. Is used to retrieve comment for config dump file
 */
class Comment implements CommentInterface
{
    /**
     * @var PlaceholderInterface
     */
    private $placeholder;

    /**
     * @var DumpConfigSourceInterface
     */
    private $source;

    /**
     * Checks whether the field is of type.
     *
     * @var TypePool
     */
    private $typePool;

    /**
     * Class ExcludeList contains list of config fields which should be excluded from config export file.
     *
     * @var ExcludeList
     */
    private $excludeList;

    /**
     * @param PlaceholderFactory $placeholderFactory
     * @param DumpConfigSourceInterface $source
     * @param TypePool|null $typePool
     * @param ExcludeList|null $excludeList
     */
    public function __construct(
        PlaceholderFactory $placeholderFactory,
        DumpConfigSourceInterface $source,
        TypePool $typePool = null,
        ExcludeList $excludeList = null
    ) {
        $this->placeholder = $placeholderFactory->create(PlaceholderFactory::TYPE_ENVIRONMENT);
        $this->source = $source;
        $this->typePool = $typePool ?: ObjectManager::getInstance()->get(TypePool::class);
        $this->excludeList = $excludeList ?: ObjectManager::getInstance()->get(ExcludeList::class);
    }

    /**
     * Retrieves comments for the configuration export file only for fields that have a sensitive type.
     *
     * @return string
     */
    public function get()
    {
        $comment = '';
        $fields = $this->source->getExcludedFields();
        foreach ($fields as $path) {
            if ($this->isSensitive($path)) {
                $comment .= "\n" . $this->placeholder->generate($path) . ' for ' . $path;
            }
        }
        if ($comment) {
            $comment = 'The configuration file doesn\'t contain sensitive data for security reasons. '
                . 'Sensitive data can be stored in the following environment variables:'
                . $comment;
        }
        return $comment;
    }

    /**
     * Checks whether the field is sensitive
     *
     * @param string $path configuration field path
     * @return bool
     */
    private function isSensitive($path)
    {
        return $this->typePool->isPresent($path, TypePool::TYPE_SENSITIVE)
            || $this->excludeList->isPresent($path);
    }
}
