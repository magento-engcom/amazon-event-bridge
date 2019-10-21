<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AmazonEventBridge\Controller\Product;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\AmazonEventBridge\Model\Product\EventManagerInterface;
use Magento\Customer\Model\Customer;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Customer\Model\Session;
use Psr\Log\LoggerInterface;

/**
 * Class EventController
 *
 * @package Magento\AmazonEventBridge\Controller\Product
 */
class EventController extends \Magento\Framework\App\Action\Action implements HttpPostActionInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var EventManagerInterface
     */
    private $productEventManager;

    /**
     * @var Session
     */
    private $session;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        Context $context,
        ProductRepositoryInterface $productRepository,
        CustomerRepositoryInterface $customerRepository,
        EventManagerInterface $productEventManager,
        LoggerInterface $logger,
        Session $session
    ) {
        parent::__construct($context);
        $this->productRepository = $productRepository;
        $this->customerRepository = $customerRepository;
        $this->productEventManager = $productEventManager;
        $this->logger = $logger;
        $this->session = $session;
    }

    /**
     * @inheritDoc
     */
    public function execute()
    {
        try {
            $product = $this->productRepository->get($this->getRequest()->getParam('product_sku'));
            $customer = $this->getCustomer()->getDataModel();

            $this->productEventManager->watchProduct($product, $customer);

            $this->messageManager->addSuccessMessage(
                __('Product has been added to your watch list.')
            );

        } catch (\Throwable $e) {
            $this->logger->error($e);
            $this->messageManager->addErrorMessage(
                __('Something went wrong. Please try again later.')
            );
        }
    }

    /**
     * @inheritdoc
     */
    private function getCustomer(): Customer
    {
        return $this->session->getCustomer();
    }
}
