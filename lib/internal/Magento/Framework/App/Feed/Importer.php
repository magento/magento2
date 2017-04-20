<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Framework\App\Feed;

/**
 * Feed importer
 */
class Importer implements \Magento\Framework\App\FeedImporterInterface
{
    /**
     * @var \Zend_Feed
     */
    private $feedProcessor;

    /**
     * @var \Magento\Framework\App\FeedFactory
     */
    private $feedFactory;

    /**
     * @param \Zend_Feed $feedProcessor
     * @param \Magento\Framework\App\FeedFactory $feedFactory
     */
    public function __construct(
        \Zend_Feed $feedProcessor,
        \Magento\Framework\App\FeedFactory $feedFactory
    ) {
        $this->feedProcessor = $feedProcessor;
        $this->feedFactory = $feedFactory;
    }

    /**
     * Get a new \Magento\Framework\App\Feed object from a custom array
     *
     * @throws \Magento\Framework\Exception\FeedImporterException
     * @param  array  $data
     * @param  string $format
     * @return \Magento\Framework\App\FeedInterface
     */
    public function importArray(array $data, $format = 'atom')
    {
        try {
            $feed = $this->feedProcessor->importArray($data, $format);
            return $this->feedFactory->create(['feed' => $feed]);   
        }
        catch (\Zend_Feed_Exception $e) {
            throw new \Magento\Framework\Exception\FeedImporterException(
                new \Magento\Framework\Phrase($e->getMessage()),
                $e
            );
        }
    }
}


