<?php
/**
 * @category Mdbhojwani
 * @package Mdbhojwani_SaveForLater
 * @author Manish Bhojwani <manishbhojwani3@gmail.com>
 * @github https://github.com/mdbhojwani
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
declare(strict_types = 1);

namespace Mdbhojwani\SaveForLater\Block;

use Magento\Catalog\Helper\Image;
use Magento\Catalog\Model\Product\Image\UrlBuilder;
use Magento\Framework\View\ConfigInterface;
use Magento\Framework\App\ObjectManager;
use Mdbhojwani\SaveForLater\Helper\Data as SaveForLaterHelper;

/**
 * SaveForLater Product Items abstract Block
 */
abstract class AbstractBlock extends \Magento\Catalog\Block\Product\AbstractProduct
{
    /**
     * SaveForLater Product Items Collection
     *
     * @var \Mdbhojwani\SaveForLater\Model\ResourceModel\Item\Collection
     */
    protected $_collection;

    /**
     * Store SaveForLater Model
     *
     * @var \Mdbhojwani\SaveForLater\Model\SaveForLater
     */
    protected $saveForLater;

    /**
     * @var SaveForLaterHelper
     */
    protected $saveForLaterHelper;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @var ConfigInterface
     */
    private $viewConfig;

    /**
     * @var UrlBuilder
     */
    private $imageUrlBuilder;

    /**
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param SaveForLaterHelper $saveForLaterHelper
     * @param array $data
     * @param ConfigInterface|null $config
     * @param UrlBuilder|null $urlBuilder
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\App\Http\Context $httpContext,
        SaveForLaterHelper $saveForLaterHelper,
        array $data = [],
        ConfigInterface $config = null,
        UrlBuilder $urlBuilder = null
    ) {
        $this->httpContext = $httpContext;
        $this->saveForLaterHelper = $saveForLaterHelper;
        parent::__construct(
            $context,
            $data
        );
        $this->viewConfig = $config ?? ObjectManager::getInstance()->get(ConfigInterface::class);
        $this->imageUrlBuilder = $urlBuilder ?? ObjectManager::getInstance()->get(UrlBuilder::class);
    }

    /**
     * Retrieve SaveForLater Data Helper
     *
     * @return \Mdbhojwani\SaveForLater\Helper\Data
     */
    protected function _getHelper()
    {
        return $this->saveForLaterHelper;
    }

    /**
     * Retrieve SaveForLater model
     *
     * @return \Mdbhojwani\SaveForLater\Model\SaveForLater
     */
    protected function _getSaveForLater()
    {
        return $this->_getHelper()->getSaveForLater();
    }

    /**
     * Prepare additional conditions to collection
     *
     * @param \Mdbhojwani\SaveForLater\Model\ResourceModel\Item\Collection $collection
     * @return \Mdbhojwani\SaveForLater\Block\Customer\SaveForLater
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _prepareCollection($collection)
    {
        return $this;
    }

    /**
     * Create SaveForLater item collection
     *
     * @return \Mdbhojwani\SaveForLater\Model\ResourceModel\Item\Collection
     */
    protected function _createSaveForLaterItemCollection()
    {
        return $this->_getSaveForLater()->getItemCollection();
    }

    /**
     * Retrieve SaveForLater Product Items collection
     *
     * @return \Mdbhojwani\SaveForLater\Model\ResourceModel\Item\Collection
     */
    public function getSaveForLaterItems()
    {
        if ($this->_collection === null) {
            $this->_collection = $this->_createSaveForLaterItemCollection();
            $this->_prepareCollection($this->_collection);
        }

        return $this->_collection;
    }

    /**
     * Retrieve SaveForLater instance
     *
     * @return \Mdbhojwani\SaveForLater\Model\SaveForLater
     */
    public function getSaveForLaterInstance()
    {
        return $this->_getSaveForLater();
    }

    /**
     * Retrieve params for Removing item from SaveForLater
     *
     * @param \Magento\Catalog\Model\Product|\Mdbhojwani\SaveForLater\Model\Item $item
     *
     * @return string
     */
    public function getItemRemoveParams($item)
    {
        return $this->_getHelper()->getRemoveParams($item);
    }

    /**
     * Retrieve Add Item to shopping cart params for POST request
     *
     * @param string|\Magento\Catalog\Model\Product|\Mdbhojwani\SaveForLater\Model\Item $item
     * @return string
     */
    public function getItemAddToCartParams($item)
    {
        return $this->_getHelper()->getAddToCartParams($item);
    }

    /**
     * Retrieve Add Item to shopping cart URL from shared SaveForLater
     *
     * @param string|\Magento\Catalog\Model\Product|\Mdbhojwani\SaveForLater\Model\Item $item
     * @return string
     */
    public function getSharedItemAddToCartUrl($item)
    {
        return $this->_getHelper()->getSharedAddToCartUrl($item);
    }

    /**
     * Retrieve URL for adding All items to shopping cart from shared SaveForLater
     *
     * @return string
     */
    public function getSharedAddAllToCartUrl()
    {
        return $this->_getHelper()->getSharedAddAllToCartUrl();
    }

    /**
     * Retrieve params for adding Product to SaveForLater
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getAddToSaveForLaterParams($product)
    {
        return $this->_getHelper()->getAddParams($product);
    }

    /**
     * Returns item configure url in SaveForLater
     *
     * @param \Magento\Catalog\Model\Product|\Mdbhojwani\SaveForLater\Model\Item $product
     *
     * @return string
     */
    public function getItemConfigureUrl($product)
    {
        return $this->_getHelper()->getConfigureUrl($product);
    }

    /**
     * Retrieve Escaped Description for SaveForLater Item
     *
     * @param \Magento\Catalog\Model\Product $item
     * @return string
     */
    public function getEscapedDescription($item)
    {
        if ($item->getDescription()) {
            return $this->escapeHtml($item->getDescription());
        }
        return '&nbsp;';
    }

    /**
     * Check SaveForLater item has description
     *
     * @param \Magento\Catalog\Model\Product $item
     * @return bool
     */
    public function hasDescription($item)
    {
        return is_string($item->getDescription()) && trim($item->getDescription()) !== '';
    }

    /**
     * Retrieve formatted Date
     *
     * @param string $date
     * @deprecated 101.1.1
     * @return string
     */
    public function getFormatedDate($date)
    {
        return $this->getFormattedDate($date);
    }

    /**
     * Retrieve formatted Date
     *
     * @param string $date
     * @return string
     */
    public function getFormattedDate($date)
    {
        return $this->formatDate($date, \IntlDateFormatter::MEDIUM);
    }

    /**
     * Check is the SaveForLater has a salable product(s)
     *
     * @return bool
     */
    public function isSaleable()
    {
        foreach ($this->getSaveForLaterItems() as $item) {
            if ($item->getProduct()->isSaleable()) {
                return true;
            }
        }

        return false;
    }

    /**
     * Retrieve SaveForLater loaded items count
     *
     * @return int
     */
    public function getSaveForLaterItemsCount()
    {
        return $this->_getSaveForLater()->getItemsCount();
    }

    /**
     * Retrieve Qty from item
     *
     * @param \Mdbhojwani\SaveForLater\Model\Item|\Magento\Catalog\Model\Product $item
     * @return float
     */
    public function getQty($item)
    {
        $qty = $item->getQty() * 1;
        if (!$qty) {
            $qty = 1;
        }
        return $qty;
    }

    /**
     * Check is the SaveForLater has items
     *
     * @return bool
     */
    public function hasSaveForLaterItems()
    {
        return $this->getSaveForLaterItemsCount() > 0;
    }

    /**
     * Retrieve URL to item Product
     *
     * @param  \Mdbhojwani\SaveForLater\Model\Item|\Magento\Catalog\Model\Product $item
     * @param  array $additional
     * @return string
     */
    public function getProductUrl($item, $additional = [])
    {
        return $this->_getHelper()->getProductUrl($item, $additional);
    }

    /**
     * Product image url getter
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getImageUrl($product)
    {
        $viewImageConfig = $this->viewConfig->getViewConfig()->getMediaAttributes(
            'Magento_Catalog',
            Image::MEDIA_TYPE_CONFIG_NODE,
            'saveforlater_small_image'
        );
        return $this->imageUrlBuilder->getUrl(
            $product->getData($viewImageConfig['type']),
            'saveforlater_small_image'
        );
    }

    /**
     * Return HTML block with price
     *
     * @param \Magento\Catalog\Model\Product\Configuration\Item\ItemInterface $item
     * @param string $priceType
     * @param string $renderZone
     * @param array $arguments
     * @return string|null
     */
    public function getItemPriceHtml(
        \Magento\Catalog\Model\Product\Configuration\Item\ItemInterface $item,
        $priceType = \Magento\Catalog\Pricing\Price\ConfiguredPriceInterface::CONFIGURED_PRICE_CODE,
        $renderZone = \Magento\Framework\Pricing\Render::ZONE_ITEM_LIST,
        array $arguments = []
    ) {
        /** @var \Magento\Framework\Pricing\Render $priceRender */
        $priceRender = $this->getLayout()->getBlock('product.price.render.default');
        $priceRender->setItem($item);
        $arguments += [
            'zone'         => $renderZone,
            'render_block' => $priceRender
        ];
        return $priceRender ? $priceRender->render($priceType, $item->getProduct(), $arguments) : null;
    }
}
