<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Model\Order\Creditmemo\Comment;

use Magento\Framework\App\ObjectManager;
use Magento\Sales\Model\Order\Creditmemo\Comment;
use Magento\Sales\Helper\SalesEntityCommentValidator;

/**
 * Sales credit memo comment validator
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
        'parent_id' => 'Parent Creditmemo Id',
        'comment' => 'Comment',
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
     * @param \Magento\Sales\Model\Order\Creditmemo\Comment $comment
     * @return array
     */
    public function validate(Comment $comment)
    {
        $commentData = $comment->getData();
        $errors = [];

        foreach ($this->required as $itemCode => $label) {
            if (empty($commentData[$itemCode])) {
                $errors[$itemCode] = sprintf('%s can not be empty', $label);
            } elseif (!$comment->hasData($itemCode)) {
                $errors[$itemCode] = sprintf('"%s" is required. Enter and try again.', $label);
            }
        }

        if (!$this->helperValidator->isEditCommentAllowed($comment)) {
            $errors['comment'] = sprintf('User is not authorized to edit comment.');
        }

        return $errors;
    }
}
