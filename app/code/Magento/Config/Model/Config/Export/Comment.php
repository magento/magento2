<?php
/**
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Config\Model\Config\Export;

use Magento\Config\App\Config\Source\DumpConfigSourceInterface;
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
     * @var DefinitionConfigFieldList
     */
    private $definitionList;

    /**
     * @param PlaceholderFactory $placeholderFactory
     * @param DumpConfigSourceInterface $source
     * @param DefinitionConfigFieldList|null $definitionConfigFieldList
     */
    public function __construct(
        PlaceholderFactory $placeholderFactory,
        DumpConfigSourceInterface $source,
        DefinitionConfigFieldList $definitionConfigFieldList = null
    ) {
        $this->placeholder = $placeholderFactory->create(PlaceholderFactory::TYPE_ENVIRONMENT);
        $this->source = $source;
        $this->definitionList = $definitionConfigFieldList ?: ObjectManager::getInstance()->get(DefinitionConfigFieldList::class);
    }

    /**
     * Retrieves comments for config export file.
     *
     * @return string
     */
    public function get()
    {
        $comment = '';
        $fields = $this->source->getExcludedFields();
        foreach ($fields as $path) {
            if ($this->definitionList->belongsTo($path, DefinitionConfigFieldList::SENSITIVE_FIELDS)) {
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
}
