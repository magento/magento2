<?php
/**
 * Copyright 2023 Adobe
 * All Rights Reserved.
 */
declare(strict_types=1);

namespace Magento\Framework\Mview\View;

class CompositeSubscriptionStatementPostprocessor implements SubscriptionStatementPostprocessorInterface
{
    /**
     * @var SubscriptionStatementPostprocessorInterface[]
     */
    private $postprocessors;

    /**
     * @param SubscriptionStatementPostprocessorInterface[] $postprocessors
     */
    public function __construct(array $postprocessors = [])
    {
        $this->postprocessors = $postprocessors;
    }

    /**
     * @inheritdoc
     */
    public function process(string $tableName, string $event, string $statement): string
    {
        foreach ($this->postprocessors as $postprocessor) {
            $statement = $postprocessor->process($tableName, $event, $statement);
        }

        return $statement;
    }
}
