<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AmazonEventBridgeApi\Api\Data;

/**
 * Represents an event that can be put to Event Bridge
 */
interface EventInterface
{
    /**
     * Get the event bus name
     *
     * The event bus that will receive the event
     *
     * @return string
     */
    public function getEventBusName(): ?string;

    /**
     * Get event resources
     *
     * AWS resources, identified by Amazon Resource Name (ARN), that the event primarily concerns
     *
     * @return string[]
     */
     public function getResources(): ?array;

    /**
     * Get event detail
     *
     * Data payload of the event
     *
     * @return array
     */
    public function getDetail(): ?array;

    /**
     * Get event detail type
     *
     * Free-form string used to decide which fields to expect in the event detail
     *
     * @return string
     */
    public function getDetailType(): ?string;

    /**
     * Get event source
     *
     * The source of the event
     *
     * @return string
     */
    public function getSource(): ?string;

    /**
     * Get time of event
     *
     * The timestamp of the event. string|DateTime or anything parsable by strtotime
     *
     * @return string
     */
    public function getTime(): ?string;
}