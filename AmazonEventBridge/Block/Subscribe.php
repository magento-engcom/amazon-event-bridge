<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\AmazonEventBridge\Block;

use Magento\Framework\Data\Helper\PostHelper;
use Magento\Catalog\Api\ProductRepositoryInterface;

class Subscribe extends \Magento\Catalog\Block\Product\View
{
    /**
     * @var PostHelper
     */
    private $postDataHelper;

    public function __construct(
        PostHelper $postDataHelper,
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\Url\EncoderInterface $urlEncoder,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\Stdlib\StringUtils $string,
        \Magento\Catalog\Helper\Product $productHelper,
        \Magento\Catalog\Model\ProductTypes\ConfigInterface $productTypeConfig,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        \Magento\Customer\Model\Session $customerSession,
        ProductRepositoryInterface $productRepository,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $urlEncoder,
            $jsonEncoder,
            $string,
            $productHelper,
            $productTypeConfig,
            $localeFormat,
            $customerSession,
            $productRepository,
            $priceCurrency,
            $data
        );

        $this->postDataHelper = $postDataHelper;
    }

    /**
     * @return string
     */
    public function getPostParams()
    {
        $item = $this->getProduct();

        $url = $this->getUrl('watchproduct/product/eventcontroller');
        $params['product_sku'] = $item->getSku();

        return $this->postDataHelper->getPostData(
            $this->escapeUrl($url),
            $params
        );
    }
}
