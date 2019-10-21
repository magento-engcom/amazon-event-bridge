<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AmazonEventBridge\Model\Product;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Customer\Api\Data\CustomerInterface;

/**
 * Interface EventManagerInterface
 *
 * @package Magento\AmazonEventBridge\Model\Product
 */
interface EventManagerInterface
{
    const EVENT_PRODUCT_WATCH = 'product_watch';

    /**
     * @param ProductInterface  $product
     * @param CustomerInterface $customer
     * @throws \RuntimeException
     * @return void
     */
    public function watchProduct(ProductInterface $product, CustomerInterface $customer): void;
}
