<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Sales\Model\Order\Shipment\Comment;

use Magento\Framework\App\ObjectManager;
use Magento\Sales\Helper\SalesEntityCommentValidator;
use Magento\Sales\Model\Order\Shipment\Comment;

/**
 * Sales shipment comment validator
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
        'parent_id' => 'Parent Shipment Id',
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
     * Shipment comment validate data
     *
     * @param \Magento\Sales\Model\Order\Shipment\Comment $comment
     * @return array
     */
    public function validate(Comment $comment)
    {
        $commentData = $comment->getData();
        $errors = [];

        $validate = $this->helperValidator->isEditCommentAllowed($comment);
        if (!$validate) {
            $errors['comment'] = sprintf('User is not authorized to edit comment.');
        }

        foreach ($this->required as $code => $commentLabel) {
            if (!$comment->hasData($code)) {
                $errors[$code] = sprintf('"%s" is required. Enter and try again.', $commentLabel);
            } elseif (empty($commentData[$code])) {
                $errors[$code] = sprintf('%s can not be empty', $commentLabel);
            }
        }

        return $errors;
    }
}
