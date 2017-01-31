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
     * Comment constructor.
     * @param PlaceholderFactory $placeholderFactory
     * @param DumpConfigSourceInterface $source
     */
    public function __construct(
        PlaceholderFactory $placeholderFactory,
        DumpConfigSourceInterface $source
    ) {
        $this->placeholder = $placeholderFactory->create(PlaceholderFactory::TYPE_ENVIRONMENT);
        $this->source = $source;
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
            $comment .= "\n" . $this->placeholder->generate($path) . ' for ' . $path ;
        }
        if ($comment) {
            $comment = 'The configuration file doesn\'t contain the sensitive data by security reason. '
                . 'The sensitive data can be stored in the next environment variables:'
                . $comment;
        }
        return $comment;
    }
}
