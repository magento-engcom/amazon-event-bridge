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

class CartObserver implements ObserverInterface
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

    /**
     * @param PutEventsInterface $putEvents
     * @param EventInterfaceFactory $eventFactory
     * @param StoreManagerInterface $storeManager
     */
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
     * Put Magento event to EventBridge
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $magentoEvent = $observer->getEvent();

        $quote = $magentoEvent->getData('quote');
        $order = $magentoEvent->getData('order');
        $cart = $magentoEvent->getData('cart');

        $detail = [];
        if($quote){
            $detail['quote'] = $this->quoteToArray($quote);
        }
        if($order){
            $detail['order'] = $this->orderToArray($order);
        }
        if($cart){
            $quote = $cart->getData('quote');
            $detail['cart'] = ['quote' => $this->quoteToArray($quote)];
        }

        $event = $this->eventFactory->create(['data' => [
            'detailType' => $magentoEvent->getName(),
            'detail' => $detail,
            'source' => $this->storeManager->getStore()->getBaseUrl(),
            'time' => date('Y-m-d h:i:s')
        ]]);

        $this->putEvents->putEvents([$event]);
    }

    /**
     * Convert Quote object to array
     *
     * @param $quote
     * @return array
     */
    private function quoteToArray($quote): array
    {
        $quoteData = $quote->getData();
        $quoteItems = [];
        foreach($quote->getAllItems() as $item){
            $itemData = $item->getData();
            $itemData['product'] = $item->getData('product')->getData();
            $quoteItems[] = $itemData;
        }
        $quoteData['items'] = $quoteItems;

        return $quoteData;
    }

    /**
     * Convert Order object to array
     *
     * @param $order
     * @return array
     */
    private function orderToArray($order): array
    {
        $orderData = $order->getData();
        $orderItems = [];
        foreach($order->getAllItems() as $item){
            $itemData = $item->getData();
            $orderItems[] = $itemData;
        }
        $orderAddresses = [];
        foreach($order->getAddresses() as $address){
            $orderAddresses[] = $address->getData();
        }
        $orderData['items'] = $orderItems;
        $orderData['addresses'] = $orderAddresses;

        return $orderData;
    }
}
