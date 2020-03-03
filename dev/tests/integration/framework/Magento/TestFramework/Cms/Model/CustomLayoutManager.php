<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Magento\TestFramework\Cms\Model;

use Magento\Cms\Api\Data\PageInterface;

/**
 * Manager allowing to fake available files.
 */
class CustomLayoutManager extends \Magento\Cms\Model\Page\CustomLayout\CustomLayoutManager
{
    /**
     * @var string[][]
     */
    private $files = [];

    /**
     * Fake available files for given page.
     *
     * Pass null to unassign fake files.
     *
     * @param int $forPageId
     * @param string[]|null $files
     * @return void
     */
    public function fakeAvailableFiles(int $forPageId, ?array $files): void
    {
        if ($files === null) {
            unset($this->files[$forPageId]);
        } else {
            $this->files[$forPageId] = $files;
        }
    }

    /**
     * @inheritDoc
     */
    public function fetchAvailableFiles(PageInterface $page): array
    {
        if (array_key_exists($page->getId(), $this->files)) {
            return $this->files[$page->getId()];
        }

        return parent::fetchAvailableFiles($page);
    }
}
