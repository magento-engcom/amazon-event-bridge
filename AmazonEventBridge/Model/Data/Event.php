<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AmazonEventBridge\Model\Data;

use Magento\AmazonEventBridgeApi\Api\Data\EventInterface;

class Event implements EventInterface
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
     * @inheritdoc
     */
    public function getEventBusName(): ?string
    {
        return $this->data['eventBusName'] ?? null;
    }

    /**
     * @inheritdoc
     */
    public function getResources(): ?array
    {
        return $this->data['resources'] ?? null;
    }

    /**
     * @inheritdoc
     */
    public function getDetail(): ?array
    {
        return $this->data['detail'] ?? null;
    }

    /**
     * @inheritdoc
     */
    public function getDetailType(): ?string
    {
        return $this->data['detailType'] ?? null;
    }

    /**
     * @inheritdoc
     */
    public function getSource(): ?string
    {
        return $this->data['source'] ?? null;
    }

    /**
     * @inheritdoc
     */
    public function getTime(): ?string
    {
        return $this->data['time'] ?? null;
    }
}
