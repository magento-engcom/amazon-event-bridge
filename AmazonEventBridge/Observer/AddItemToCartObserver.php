<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AmazonEventBridge\Observer;

use Magento\AmazonEventBridgeApi\Api\Data\EventInterfaceFactory;
use Magento\AmazonEventBridgeApi\Api\PutEventsInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Model\StoreManagerInterface;

class AddItemToCartObserver implements ObserverInterface
{
    /**
     * @var PutEventsInterface
     */
    private $putEvents;

    /**
     * @var EventInterfaceFactory
     */
    private $eventFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        PutEventsInterface $putEvents,
        EventInterfaceFactory $eventFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->putEvents = $putEvents;
        $this->eventFactory = $eventFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * Put "add to cart" event to AWS
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $magentoEvent = $observer->getEvent();
        $magentoEventData = $magentoEvent->getData('quote_item')->getData();
        $magentoEventData['product'] = $magentoEventData['product']->getData();

        $event = $this->eventFactory->create(['data' => [
            'detailType' => $magentoEvent->getName(),
            'detail' => $magentoEventData,
            'source' => $this->storeManager->getStore()->getBaseUrl(),
            'time' => date('Y-m-d h:i:s')
        ]]);

        $this->putEvents->putEvents([$event]);
    }
}
