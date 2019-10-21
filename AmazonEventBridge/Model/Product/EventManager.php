<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AmazonEventBridge\Model\Product;

use Magento\AmazonEventBridgeApi\Api\PutEventsInterface;
use \Magento\AmazonEventBridgeApi\Api\Data\EventInterface;
use \Magento\AmazonEventBridgeApi\Api\Data\EventInterfaceFactory;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Class EventManager
 *
 * @package Magento\AmazonEventBridge\Model\Product
 */
class EventManager implements EventManagerInterface
{
    /**
     * @var PutEventsInterface
     */
    private $putEvent;

    /**
     * @var EventInterfaceFactory
     */
    private $eventFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * EventManager constructor.
     *
     * @param PutEventsInterface $putEvent
     * @param EventInterfaceFactory $eventFactory
     * @param StoreManagerInterface $storeManager
     * @param LoggerInterface $logger
     */
    public function __construct(
        PutEventsInterface $putEvent,
        EventInterfaceFactory $eventFactory,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger
    ) {
        $this->putEvent = $putEvent;
        $this->eventFactory = $eventFactory;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
    }

    /**
     * @inheritdoc
     */
    public function watchProduct(ProductInterface $product, CustomerInterface $customer): void
    {
        $detail = [
            'product' => [
                'product_id' => $product->getId(),
                'product_sku' => $product->getSku(),
            ],
            'customer' => [
                'email' => $customer->getEmail()
            ],
        ];

        try {
            /** @var EventInterface $event */
            $event = $this->eventFactory->create(['data' => [
                'detailType' => EventManagerInterface::EVENT_PRODUCT_WATCH,
                'detail' => $detail,
                'source' => $this->storeManager->getStore()->getCode(),
                'time' => date('Y-m-d h:i:s'),
            ]]);
        } catch (NoSuchEntityException $e) {
            $this->logger->warning($e);
            throw new \RuntimeException($e->getMessage(), $e->getCode(), $e);
        }

        $this->putEvent->putEvents([$event]);
    }
}
