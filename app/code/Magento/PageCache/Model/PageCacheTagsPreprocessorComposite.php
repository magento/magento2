<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\PageCache\Model;

use InvalidArgumentException;
use Magento\Framework\App\RequestInterface;
use Magento\PageCache\Model\Spi\PageCacheTagsPreprocessorInterface;

/**
 * Composite page cache preprocessors
 */
class PageCacheTagsPreprocessorComposite implements PageCacheTagsPreprocessorInterface
{
    /**
     * @var PageCacheTagsPreprocessorInterface[][]
     */
    private $preprocessors;
    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @param RequestInterface $request
     * @param PageCacheTagsPreprocessorInterface[][] $preprocessors
     */
    public function __construct(
        RequestInterface $request,
        array $preprocessors = []
    ) {
        foreach ($preprocessors as $group) {
            foreach ($group as $preprocessor) {
                if (!$preprocessor instanceof PageCacheTagsPreprocessorInterface) {
                    throw new InvalidArgumentException(
                        sprintf(
                            'Instance of %s is expected, got %s instead.',
                            PageCacheTagsPreprocessorInterface::class,
                            get_class($preprocessor)
                        )
                    );
                }
            }
        }
        $this->preprocessors = $preprocessors;
        $this->request = $request;
    }

    /**
     * @inheritDoc
     */
    public function process(array $tags): array
    {
        $forwardInfo = $this->request->getBeforeForwardInfo();
        $actionName = $forwardInfo
            ? implode('_', [$forwardInfo['route_name'], $forwardInfo['controller_name'], $forwardInfo['action_name']])
            : $this->request->getFullActionName();
        if (isset($this->preprocessors[$actionName])) {
            foreach ($this->preprocessors[$actionName] as $preprocessor) {
                $tags = $preprocessor->process($tags);
            }
        }
        return $tags;
    }
}
