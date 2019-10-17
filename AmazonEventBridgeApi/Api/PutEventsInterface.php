<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AmazonEventBridgeApi\Api;

use Magento\AmazonEventBridgeApi\Api\Data\EventInterface;

/**
 * Service to put events to Amazon Event Bridge API
 */
interface PutEventsInterface
{
    /**
     * @param EventInterface[] $events
     * @return void
     */
    public function putEvents(array $events): void;
}
