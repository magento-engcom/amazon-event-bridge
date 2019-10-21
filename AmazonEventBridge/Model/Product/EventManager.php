<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\AmazonEventBridge\Model\Product;

use Magento\AmazonEventBridgeApi\Api\Data\EventInterface;
use Magento\AmazonEventBridgeApi\Api\Data\EventInterfaceFactory;
use Magento\AmazonEventBridgeApi\Api\PutEventsInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\DateTimeFactory;
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
     * @var DateTimeFactory
     */
    private $dateFactory;

    /**
     * EventManager constructor.
     *
     * @param PutEventsInterface $putEvent
     * @param EventInterfaceFactory $eventFactory
     * @param StoreManagerInterface $storeManager
     * @param LoggerInterface $logger
     * @param DateTimeFactory $dateFactory
     */
    public function __construct(
        PutEventsInterface $putEvent,
        EventInterfaceFactory $eventFactory,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger,
        DateTimeFactory $dateFactory
    ) {
        $this->putEvent = $putEvent;
        $this->eventFactory = $eventFactory;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
        $this->dateFactory = $dateFactory;
    }

    /**
     * @inheritdoc
     */
    public function watchProduct(ProductInterface $product, CustomerInterface $customer): void
    {
        $customerEmail = $customer->getEmail();

        $this->validateEmail($customerEmail);

        $detail = [
            'product' => [
                'product_id' => $product->getId(),
                'product_sku' => $product->getSku(),
            ],
            'customer' => [
                'email' => $customerEmail
            ],
        ];

        try {
            /** @var EventInterface $event */
            $event = $this->eventFactory->create(['data' => [
                'detailType' => EventManagerInterface::EVENT_PRODUCT_WATCH,
                'detail' => $detail,
                'source' => $this->storeManager->getStore()->getCode(),
                'time' => $this->dateFactory->create()->gmtDate(),
            ]]);
        } catch (NoSuchEntityException $e) {
            $this->logger->warning($e);
            throw new \RuntimeException($e->getMessage(), $e->getCode(), $e);
        }

        $this->putEvent->putEvents([$event]);
    }

    /**
     * Validate customer email
     *
     * @param string|null $email
     * @throws LocalizedException
     * @throws \Zend_Validate_Exception
     */
    private function validateEmail($email): void
    {
        if (!\Zend_Validate::is($email, \Magento\Framework\Validator\EmailAddress::class)) {
            throw new LocalizedException(
                __('The "%1" email address is incorrect. Verify the email address and try again.', $email)
            );
        }
    }
}
