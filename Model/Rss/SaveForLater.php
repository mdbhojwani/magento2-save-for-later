<?php
/**
 * @category Mdbhojwani
 * @package Mdbhojwani_SaveForLater
 * @author Manish Bhojwani <manishbhojwani3@gmail.com>
 * @github https://github.com/mdbhojwani
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
declare(strict_types = 1);

namespace Mdbhojwani\SaveForLater\Model\Rss;

use Magento\Framework\App\Rss\DataProviderInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * SaveForLater RSS model
 */
class SaveForLater implements DataProviderInterface
{
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * System event manager
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $eventManager;

    /**
     * Parent layout of the block
     *
     * @var \Magento\Framework\View\LayoutInterface
     */
    protected $layout;

    /**
     * @var \Mdbhojwani\SaveForLater\Helper\Data
     */
    protected $saveForLaterHelper;

    /**
     * @var \Magento\Catalog\Helper\Output
     */
    protected $outputHelper;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $imageHelper;

    /**
     * @var \Mdbhojwani\SaveForLater\Block\Customer\SaveForLater
     */
    protected $saveForLaterBlock;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $request;

    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    protected $customerFactory;

    /**
     * SaveForLater constructor.
     *
     * @param \Mdbhojwani\SaveForLater\Helper\Rss $saveForLaterHelper
     * @param \Mdbhojwani\SaveForLater\Block\Customer\SaveForLater $saveForLaterBlock
     * @param \Magento\Catalog\Helper\Output $outputHelper
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Customer\Model\CustomerFactory $customerFactory
     * @param \Magento\Framework\View\LayoutInterface $layout
     * @param \Magento\Framework\App\RequestInterface $request
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Mdbhojwani\SaveForLater\Helper\Rss $saveForLaterHelper,
        \Mdbhojwani\SaveForLater\Block\Customer\SaveForLater $saveForLaterBlock,
        \Magento\Catalog\Helper\Output $outputHelper,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\View\LayoutInterface $layout,
        \Magento\Framework\App\RequestInterface $request
    ) {
        $this->saveForLaterHelper = $saveForLaterHelper;
        $this->saveForLaterBlock = $saveForLaterBlock;
        $this->outputHelper = $outputHelper;
        $this->imageHelper = $imageHelper;
        $this->urlBuilder = $urlBuilder;
        $this->scopeConfig = $scopeConfig;
        $this->eventManager = $eventManager;
        $this->customerFactory = $customerFactory;
        $this->layout = $layout;
        $this->request = $request;
    }

    /**
     * Check if RSS feed allowed
     *
     * @return mixed
     */
    public function isAllowed()
    {
        return $this->scopeConfig->isSetFlag('rss/saveforlater/active', ScopeInterface::SCOPE_STORE)
            && $this->getSaveForLater()->getCustomerId() === $this->saveForLaterHelper->getCustomer()->getId();
    }

    /**
     * Get RSS feed items
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getRssData()
    {
        $saveForLater = $this->getSaveForLater();
        if ($saveForLater->getId()) {
            $data = $this->getHeader();

            /** @var $saveForLaterItem \Mdbhojwani\SaveForLater\Model\Item */
            foreach ($saveForLater->getItemCollection() as $saveForLaterItem) {
                /* @var $product \Magento\Catalog\Model\Product */
                $product = $saveForLaterItem->getProduct();
                $productUrl = $this->saveForLaterBlock->getProductUrl($product, ['_rss' => true]);
                $product->setAllowedInRss(true);
                $product->setAllowedPriceInRss(true);
                $product->setProductUrl($productUrl);
                $args = ['product' => $product];

                $this->eventManager->dispatch('rss_saveforlater_xml_callback', $args);

                if (!$product->getAllowedInRss()) {
                    continue;
                }

                $description = '<table><tr><td><a href="' . $productUrl . '"><img src="'
                    . $this->imageHelper->init($product, 'rss_thumbnail')->getUrl()
                    . '" border="0" align="left" height="75" width="75"></a></td>'
                    . '<td style="text-decoration:none;">'
                    . $this->outputHelper->productAttribute(
                        $product,
                        $product->getShortDescription(),
                        'short_description'
                    ) . '<p>';

                if ($product->getAllowedPriceInRss()) {
                    $description .= $this->getProductPriceHtml($product);
                }
                $description .= '</p>';

                if (is_string($product->getDescription()) && trim($product->getDescription()) !== '') {
                    $description .= '<p>' . __('Comment:') . ' '
                        . $this->outputHelper->productAttribute(
                            $product,
                            $product->getDescription(),
                            'description'
                        ) . '<p>';
                }
                $description .= '</td></tr></table>';

                $data['entries'][] = ([
                    'title' => $product->getName(),
                    'link' => $productUrl,
                    'description' => $description,
                ]);
            }
        } else {
            $data = [
                'title' => __('We cannot retrieve the Save For Later.')->render(),
                'description' => __('We cannot retrieve the Save For Later.')->render(),
                'link' => $this->urlBuilder->getUrl(),
                'charset' => 'UTF-8',
            ];
        }

        return $data;
    }

    /**
     * GetCacheKey
     *
     * @return string
     */
    public function getCacheKey()
    {
        return 'rss_saveforlater_data_' . $this->getSaveForLater()->getId();
    }

    /**
     * Get Cache Lifetime
     *
     * @return int
     */
    public function getCacheLifetime()
    {
        return 60;
    }

    /**
     * Get data for Header section of RSS feed
     *
     * @return array
     */
    public function getHeader()
    {
        $customerId = $this->getSaveForLater()->getCustomerId();
        $customer = $this->customerFactory->create()->load($customerId);
        $title = __('%1\'s Save For Later', $customer->getName())->render();
        $newUrl = $this->urlBuilder->getUrl(
            'saveforlater/shared/index',
            ['code' => $this->getSaveForLater()->getSharingCode()]
        );

        return ['title' => $title, 'description' => $title, 'link' => $newUrl, 'charset' => 'UTF-8'];
    }

    /**
     * Retrieve SaveForLater model
     *
     * @return \Mdbhojwani\SaveForLater\Model\SaveForLater
     */
    protected function getSaveForLater()
    {
        $saveForLater = $this->saveForLaterHelper->getSaveForLater();
        return $SaveForLater;
    }

    /**
     * Return HTML block with product price
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getProductPriceHtml(\Magento\Catalog\Model\Product $product)
    {
        $price = '';
        /** @var \Magento\Framework\Pricing\Render $priceRender */
        $priceRender = $this->layout->getBlock('product.price.render.default');
        if (!$priceRender) {
            $priceRender = $this->layout->createBlock(
                \Magento\Framework\Pricing\Render::class,
                'product.price.render.default',
                ['data' => ['price_render_handle' => 'catalog_product_prices']]
            );
        }
        if ($priceRender) {
            $price = $priceRender->render(
                'saveforlater_configured_price',
                $product,
                ['zone' => \Magento\Framework\Pricing\Render::ZONE_ITEM_LIST]
            );
        }
        return $price;
    }

    /**
     * @inheritdoc
     */
    public function getFeeds()
    {
        return [];
    }

    /**
     * @inheritdoc
     */
    public function isAuthRequired()
    {
        if ($this->request->getParam('sharing_code') == $this->getSaveForLater()->getSharingCode()) {
            return false;
        }
        return true;
    }
}
