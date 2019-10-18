<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AmazonEventBridge\Model\Consumer\Data;

/**
 * Representation of SQS message
 */
class SqsMessage
{
    /**
     * @var array
     */
    private $data;

    /**
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->data = $data;
    }

    /**
     * Get message body
     *
     * @return string
     */
    public function getBody(): string
    {
        return $this->data['body'];
    }

    /**
     * Get message ID
     *
     * @return string
     */
    public function getMessageId(): string
    {
        return $this->data['messageId'];
    }

    /**
     * Get message receipt handle
     *
     * @return string
     */
    public function getReceiptHandle(): string
    {
        return $this->data['receiptHandle'];
    }
}
