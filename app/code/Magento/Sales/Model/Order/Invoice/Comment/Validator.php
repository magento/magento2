<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Model\Order\Invoice\Comment;

use Magento\Sales\Model\Order\Invoice\Comment;
use Magento\Framework\App\ObjectManager;
use Magento\Sales\Helper\SalesEntityCommentValidator;

/**
 * Sales invoice comment validator
 */
class Validator
{
    /**
     * Sales entity comment validator
     * @var SalesEntityCommentValidator
     */
    private SalesEntityCommentValidator $helperValidator;

    /**
     * Required field
     *
     * @var array
     */
    protected $required = [
        'comment' => 'Comment',
        'parent_id' => 'Parent Invoice Id',
    ];

    /**
     * @param SalesEntityCommentValidator|null $helperValidator
     */
    public function __construct(
        SalesEntityCommentValidator $helperValidator = null
    ) {
        $this->helperValidator = $helperValidator ??
            ObjectManager::getInstance()->get(SalesEntityCommentValidator::class);
    }

    /**
     * Validate data
     *
     * @param Comment $comment
     * @return array
     */
    public function validate(Comment $comment)
    {
        $errors = [];
        $commentData = $comment->getData();

        if (!$this->helperValidator->isEditCommentAllowed($comment)) {
            $errors['comment'] = sprintf('User is not authorized to edit comment.');
        }

        foreach ($this->required as $code => $itemLabel) {
            if (!$comment->hasData($code)) {
                $errors[$code] = sprintf('"%s" is required. Enter and try again.', $itemLabel);
            } elseif (empty($commentData[$code])) {
                $errors[$code] = sprintf('%s can not be empty', $itemLabel);
            }
        }

        return $errors;
    }
}
