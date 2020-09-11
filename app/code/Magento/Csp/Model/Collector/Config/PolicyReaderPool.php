<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Csp\Model\Collector\Config;

/**
 * Pool of readers.
 */
class PolicyReaderPool
{
    /**
     * @var PolicyReaderInterface[]
     */
    private $readers;

    /**
     * @param PolicyReaderInterface[] $readers
     */
    public function __construct(array $readers)
    {
        $this->readers = $readers;
    }

    /**
     * Find a reader for the policy.
     *
     * @param string $id
     * @return PolicyReaderInterface
     * @throws \RuntimeException When failed to find a reader for given policy.
     */
    public function getReader(string $id): PolicyReaderInterface
    {
        foreach ($this->readers as $reader) {
            if ($reader->canRead($id)) {
                return $reader;
            }
        }

        throw new \RuntimeException(sprintf('Failed to find a config reader for policy #%s', $id));
    }
}
