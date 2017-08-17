<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
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
 * @api
 * @since 100.1.2
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
     * Checker for config type.
     *
     * @var TypePool
     */
    private $typePool;

    /**
     * @param PlaceholderFactory $placeholderFactory
     * @param DumpConfigSourceInterface $source
     * @param TypePool|null $typePool The checker for config type
     * @since 100.1.2
     */
    public function __construct(
        PlaceholderFactory $placeholderFactory,
        DumpConfigSourceInterface $source,
        TypePool $typePool = null
    ) {
        $this->placeholder = $placeholderFactory->create(PlaceholderFactory::TYPE_ENVIRONMENT);
        $this->source = $source;
        $this->typePool = $typePool ?: ObjectManager::getInstance()->get(TypePool::class);
    }

    /**
     * Retrieves comments for the configuration export file.
     *
     * If there are sensitive fields in the configuration fields,
     * a list with descriptions of these fields will be added to the comments.
     *
     * @return string
     * @since 100.1.2
     */
    public function get()
    {
        $comments = [];

        foreach ($this->source->getExcludedFields() as $path) {
            if ($this->typePool->isPresent($path, TypePool::TYPE_SENSITIVE)) {
                $comments[] = $this->placeholder->generate($path) . ' for ' . $path;
            }
        }

        if (!empty($comments)) {
            $comments = array_merge([
                'Shared configuration was written to config.php and system-specific configuration to env.php.',
                'Shared configuration file (config.php) doesn\'t contain sensitive data for security reasons.',
                'Sensitive data can be stored in the following environment variables:'
            ], $comments);
        }

        return implode(PHP_EOL, $comments);
    }
}
