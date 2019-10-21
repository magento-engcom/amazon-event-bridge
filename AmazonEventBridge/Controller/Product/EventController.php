<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AmazonEventBridge\Controller\Product;

use Magento\AmazonEventBridge\Model\Product\EventManagerInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Phrase;

/**
 * Class EventController
 *
 * @package Magento\AmazonEventBridge\Controller\Product
 */
class EventController extends \Magento\Framework\App\Action\Action
    implements HttpPostActionInterface, CsrfAwareActionInterface
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

    /**
     * EventController constructor.
     * @param Context $context
     * @param ProductRepositoryInterface $productRepository
     * @param CustomerRepositoryInterface $customerRepository
     * @param EventManagerInterface $productEventManager
     * @param LoggerInterface $logger
     * @param Session $session
     */
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

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
    }

    /**
     * @inheritDoc
     */
    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        /** @var Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());

        return new InvalidRequestException(
            $resultRedirect,
            [new Phrase('Invalid Form Key. Please refresh the page.')]
        );
    }

    /**
     * @inheritDoc
     */
    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    private function getCustomer(): Customer
    {
        return $this->session->getCustomer();
    }
}
