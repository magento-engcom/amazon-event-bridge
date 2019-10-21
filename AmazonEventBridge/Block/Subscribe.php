<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AmazonEventBridge\Block;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Data\Helper\PostHelper;
use Magento\Framework\Escaper;
use Magento\Store\Model\StoreManagerInterface;

class Subscribe extends \Magento\Catalog\Block\Product\View
{

    public function getPostParams()
    {
        $storeManager = ObjectManager::getInstance()->get(StoreManagerInterface::class);
        $escaper = ObjectManager::getInstance()->get(Escaper::class);
        $postHelper = ObjectManager::getInstance()->get(PostHelper::class);
        $item = $this->getProduct();

        $url = $storeManager->getStore()->getUrl('watchproduct/product/eventcontroller');
        $params['product_sku'] = $item->getSku();

        return $postHelper->getPostData(
            $escaper->escapeUrl($url),
            $params
        );
    }
}
