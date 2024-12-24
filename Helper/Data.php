<?php
/**
 * @category Mdbhojwani
 * @package Mdbhojwani_SaveForLater
 * @author Manish Bhojwani <manishbhojwani3@gmail.com>
 * @github https://github.com/mdbhojwani
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
declare(strict_types = 1);

namespace Mdbhojwani\SaveForLater\Helper;

use Magento\Catalog\Model\Product;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DataObject;
use Magento\Framework\Escaper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Mdbhojwani\SaveForLater\Controller\SaveForLaterProviderInterface;
use Mdbhojwani\SaveForLater\Model\Item;

/**
 * SaveForLater Data Helper
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Config key 'Display SaveForLater Summary'
     */
    public const XML_PATH_SAVEFORLATER_LINK_USE_QTY = 'saveforlater/saveforlater_link/use_qty';

    /**
     * Config key 'Display Out of Stock Products'
     */
    public const XML_PATH_CATALOGINVENTORY_SHOW_OUT_OF_STOCK = 'cataloginventory/options/show_out_of_stock';

    /**
     * Currently logged in customer
     *
     * @var \Magento\Customer\Api\Data\CustomerInterface
     */
    protected $_currentCustomer;

    /**
     * Customer SaveForLater instance
     *
     * @var \Mdbhojwani\SaveForLater\Model\SaveForLater
     */
    protected $saveForLater;

    /**
     * SaveForLater Product Items Collection
     *
     * @var \Mdbhojwani\SaveForLater\Model\ResourceModel\Item\Collection
     */
    protected $_productCollection;

    /**
     * SaveForLater Items Collection
     *
     * @var \Mdbhojwani\SaveForLater\Model\ResourceModel\Item\Collection
     */
    protected $saveForLaterItemCollection;

    /**
     * Magento framework Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Mdbhojwani\SaveForLater\Model\SaveForLaterFactory
     */
    protected $saveForLaterFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Data\Helper\PostHelper
     */
    protected $_postDataHelper;

    /**
     * @var \Magento\Customer\Helper\View
     */
    protected $_customerViewHelper;

    /**
     * @var \Mdbhojwani\SaveForLater\Controller\SaveForLaterProviderInterface
     */
    protected $saveForLaterProvider;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Mdbhojwani\SaveForLater\Model\SaveForLaterFactory $saveForLaterFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Data\Helper\PostHelper $postDataHelper
     * @param \Magento\Customer\Helper\View $customerViewHelper
     * @param SaveForLaterProviderInterface $saveForLaterProvider
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param Escaper $escaper
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Customer\Model\Session $customerSession,
        \Mdbhojwani\SaveForLater\Model\SaveForLaterFactory $saveForLaterFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        \Magento\Customer\Helper\View $customerViewHelper,
        SaveForLaterProviderInterface $saveForLaterProvider,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        Escaper $escaper = null
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_customerSession = $customerSession;
        $this->saveForLaterFactory = $saveForLaterFactory;
        $this->_storeManager = $storeManager;
        $this->_postDataHelper = $postDataHelper;
        $this->_customerViewHelper = $customerViewHelper;
        $this->saveForLaterProvider = $saveForLaterProvider;
        $this->productRepository = $productRepository;
        $this->escaper = $escaper ?? ObjectManager::getInstance()->get(Escaper::class);
        parent::__construct($context);
    }

    /**
     * Retrieve customer login status
     *
     * @return bool
     */
    protected function _isCustomerLogIn()
    {
        return $this->_customerSession->isLoggedIn();
    }

    /**
     * Retrieve logged in customer
     *
     * @return \Magento\Customer\Api\Data\CustomerInterface
     */
    protected function _getCurrentCustomer()
    {
        return $this->getCustomer();
    }

    /**
     * Set current customer
     *
     * @param \Magento\Customer\Api\Data\CustomerInterface $customer
     * @return void
     */
    public function setCustomer(\Magento\Customer\Api\Data\CustomerInterface $customer)
    {
        $this->_currentCustomer = $customer;
    }

    /**
     * Retrieve current customer
     *
     * @return \Magento\Customer\Api\Data\CustomerInterface|null
     */
    public function getCustomer()
    {
        if (!$this->_currentCustomer && $this->_customerSession->isLoggedIn()) {
            $this->_currentCustomer = $this->_customerSession->getCustomerData();
        }
        return $this->_currentCustomer;
    }

    /**
     * Retrieve SaveForLater by logged in customer
     *
     * @return \Mdbhojwani\SaveForLater\Model\SaveForLater
     */
    public function getSaveForLater()
    {
        if ($this->saveForLater === null) {
            if ($this->_coreRegistry->registry('shared_saveforlater')) {
                $this->saveForLater = $this->_coreRegistry->registry('shared_saveforlater');
            } else {
                $this->saveForLater = $this->saveForLaterProvider->getSaveForLater();
            }
        }
        return $this->saveForLater;
    }

    /**
     * Retrieve SaveForLater item count (include config settings)
     *
     * Used in top link menu only
     *
     * @return int
     */
    public function getItemCount()
    {
        $storedDisplayType = $this->_customerSession->getSaveForLaterDisplayType();
        $currentDisplayType = $this->scopeConfig->getValue(
            self::XML_PATH_SAVEFORLATER_LINK_USE_QTY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        $storedDisplayOutOfStockProducts = $this->_customerSession->getDisplayOutOfStockProducts();
        $currentDisplayOutOfStockProducts = $this->scopeConfig->getValue(
            self::XML_PATH_CATALOGINVENTORY_SHOW_OUT_OF_STOCK,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        if (!$this->_customerSession->hasSaveForLaterItemCount() ||
            $currentDisplayType != $storedDisplayType ||
            $this->_customerSession->hasDisplayOutOfStockProducts() ||
            $currentDisplayOutOfStockProducts != $storedDisplayOutOfStockProducts
        ) {
            $this->calculate();
        }

        return $this->_customerSession->getSaveForLaterItemCount();
    }

    /**
     * Create SaveForLater item collection
     *
     * @return \Mdbhojwani\SaveForLater\Model\ResourceModel\Item\Collection
     */
    protected function _createSaveForLaterItemCollection()
    {
        return $this->getSaveForLater()->getItemCollection();
    }

    /**
     * Retrieve SaveForLater items collection
     *
     * @return \Mdbhojwani\SaveForLater\Model\ResourceModel\Item\Collection
     */
    public function getSaveForLaterItemCollection()
    {
        if ($this->saveForLaterItemCollection === null) {
            $this->saveForLaterItemCollection = $this->_createSaveForLaterItemCollection();
        }
        return $this->saveForLaterItemCollection;
    }

    /**
     * Retrieve Item Store for URL
     *
     * @param \Magento\Catalog\Model\Product|\Mdbhojwani\SaveForLater\Model\Item $item
     * @return \Magento\Store\Model\Store
     */
    protected function _getUrlStore($item)
    {
        $storeId = null;
        $product = null;
        if ($item instanceof \Mdbhojwani\SaveForLater\Model\Item) {
            $product = $item->getProduct();
        } elseif ($item instanceof \Magento\Catalog\Model\Product) {
            $product = $item;
        }
        if ($product) {
            if ($product->isVisibleInSiteVisibility()) {
                $storeId = $product->getStoreId();
            } else {
                if ($product->hasUrlDataObject()) {
                    $storeId = $product->getUrlDataObject()->getStoreId();
                }
            }
        }
        return $this->_storeManager->getStore($storeId);
    }

    /**
     * Retrieve params for removing item from SaveForLater
     *
     * @param \Magento\Catalog\Model\Product|\Mdbhojwani\SaveForLater\Model\Item $item
     * @param bool $addReferer
     * @return string
     */
    public function getRemoveParams($item, $addReferer = false)
    {
        $url = $this->_getUrl('saveforlater/index/remove');
        $params = ['item' => $item->getSaveforlaterItemId()];
        $params[ActionInterface::PARAM_NAME_URL_ENCODED] = '';

        if ($addReferer) {
            $params = $this->addRefererToParams($params);
        }

        return $this->_postDataHelper->getPostData($url, $params);
    }

    /**
     * Retrieve URL for configuring item from SaveForLater
     *
     * @param \Magento\Catalog\Model\Product|\Mdbhojwani\SaveForLater\Model\Item $item
     * @return string
     */
    public function getConfigureUrl($item)
    {
        $query = $this->getItemQueryOptions($item);
        $url = $this->_getUrl(
            'saveforlater/index/configure',
            [
                'id' => $item->getSaveForLaterItemId(),
                'product_id' => $item->getProductId(),
                'qty' => (int)$item->getQty()
            ]
        );
        $url .= (isset($query['fragment']) && count($query['fragment'])) ?
            '#' . http_build_query($query['fragment']) : '';
        return $url;
    }

    /**
     * Retrieve params for adding product to SaveForLater
     *
     * @param \Magento\Catalog\Model\Product|\Mdbhojwani\SaveForLater\Model\Item $item
     * @param array $params
     * @return string
     */
    public function getAddParams($item, array $params = [])
    {
        $productId = null;
        if ($item instanceof \Magento\Catalog\Model\Product) {
            $productId = (int) $item->getEntityId();
        }
        if ($item instanceof \Mdbhojwani\SaveForLater\Model\Item) {
            $productId = (int) $item->getProductId();
        }

        $url = $this->_getUrlStore($item)->getUrl('saveforlater/index/add');
        if ($productId) {
            $params['product'] = $productId;
        }

        return $this->_postDataHelper->getPostData(
            $this->escaper->escapeUrl($url),
            $params
        );
    }

    /**
     * Retrieve params for adding product to SaveForLater
     *
     * @param int $itemId
     * @return string
     */
    public function getMoveFromCartParams($itemId)
    {
        $url = $this->_getUrl('saveforlater/index/fromcart');
        $params = ['item' => $itemId];
        return $this->_postDataHelper->getPostData($url, $params);
    }

    /**
     * Retrieve params for updating product in SaveForLater
     *
     * @param \Magento\Catalog\Model\Product|\Mdbhojwani\SaveForLater\Model\Item $item
     * @return string|false
     */
    public function getUpdateParams($item)
    {
        $itemId = null;
        if ($item instanceof \Magento\Catalog\Model\Product) {
            $itemId = $item->getSaveForLaterItemId();
            $productId = $item->getId();
        }
        if ($item instanceof \Mdbhojwani\SaveForLater\Model\Item) {
            $itemId = $item->getId();
            $productId = $item->getProduct()->getId();
        }

        $url = $this->_getUrl('saveforlater/index/updateItemOptions');
        if ($itemId) {
            $params = ['id' => $itemId, 'product' => $productId, 'qty' => $item->getQty()];
            return $this->_postDataHelper->getPostData($url, $params);
        }

        return false;
    }

    /**
     * Retrieve params for adding item to shopping cart
     *
     * @param string|\Magento\Catalog\Model\Product|\Mdbhojwani\SaveForLater\Model\Item $item
     * @return string
     */
    public function getAddToCartUrl($item)
    {
        return $this->_getUrlStore($item)->getUrl('saveforlater/index/cart', $this->_getCartUrlParameters($item));
    }

    /**
     * Retrieve URL for adding item to shopping cart
     *
     * @param string|\Magento\Catalog\Model\Product|\Mdbhojwani\SaveForLater\Model\Item $item
     * @param bool $addReferer
     * @return string
     */
    public function getAddToCartParams($item, $addReferer = false)
    {
        $params = $this->_getCartUrlParameters($item);
        $params[ActionInterface::PARAM_NAME_URL_ENCODED] = '';

        if ($addReferer) {
            $params = $this->addRefererToParams($params);
        }

        return $this->_postDataHelper->getPostData(
            $this->_getUrlStore($item)->getUrl('saveforlater/index/cart'),
            $params
        );
    }

    /**
     * Add UENC referer to params
     *
     * @param array $params
     * @return array
     */
    public function addRefererToParams(array $params)
    {
        $params[ActionInterface::PARAM_NAME_URL_ENCODED] =
            $this->urlEncoder->encode($this->_getRequest()->getServer('HTTP_REFERER'));
        return $params;
    }

    /**
     * Retrieve URL for adding item to shopping cart from shared SaveForLater
     *
     * @param string|\Magento\Catalog\Model\Product|\Mdbhojwani\SaveForLater\Model\Item $item
     * @return string
     */
    public function getSharedAddToCartUrl($item)
    {
        return $this->_postDataHelper->getPostData(
            $this->_getUrlStore($item)->getUrl('saveforlater/shared/cart'),
            $this->_getCartUrlParameters($item)
        );
    }

    /**
     * Retrieve URL for adding All items to shopping cart from shared SaveForLater
     *
     * @return string
     */
    public function getSharedAddAllToCartUrl()
    {
        return $this->_postDataHelper->getPostData(
            $this->_storeManager->getStore()->getUrl('*/*/allcart', ['_current' => true])
        );
    }

    /**
     * Get cart URL parameters
     *
     * @param string|\Magento\Catalog\Model\Product|\Mdbhojwani\SaveForLater\Model\Item $item
     * @return array
     */
    protected function _getCartUrlParameters($item)
    {
        $params = [
            'item' => is_string($item) ? $item : $item->getSaveForLaterItemId(),
        ];
        if ($item instanceof \Mdbhojwani\SaveForLater\Model\Item) {
            $params['qty'] = $item->getQty();
        }
        return $params;
    }

    /**
     * Retrieve customer SaveForLater url
     *
     * @param int $saveForLaterId
     * @return string
     */
    public function getListUrl($saveForLaterId = null)
    {
        $params = [];
        if ($saveForLaterId) {
            $params['saveforlater_id'] = $saveForLaterId;
        }
        return $this->_getUrl('saveforlater', $params);
    }

    /**
     * Check is allow SaveForLater module
     *
     * @return bool
     */
    public function isAllow()
    {
        $isOutputEnabled  = $this->_moduleManager->isOutputEnabled($this->_getModuleName());

        $isSaveForLaterActive = $this->scopeConfig->getValue(
            'saveforlater/general/active',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        return $isOutputEnabled && $isSaveForLaterActive;
    }

    /**
     * Check is allow SaveForLater action in shopping cart
     *
     * @return bool
     */
    public function isAllowInCart()
    {
        return $this->isAllow() && $this->getCustomer();
    }

    /**
     * Retrieve customer name
     *
     * @return string|null
     */
    public function getCustomerName()
    {
        return $this->getCustomer()
            ? $this->_customerViewHelper->getCustomerName($this->getCustomer())
            : null;
    }

    /**
     * Retrieve RSS URL
     *
     * @param int|string|null $saveForLaterId
     * @return string
     */
    public function getRssUrl($saveForLaterId = null)
    {
        $params = [];
        $customer = $this->_getCurrentCustomer();
        if ($customer) {
            $key = $customer->getId() . ',' . $customer->getEmail();
            $params = ['data' => $this->urlEncoder->encode($key), '_secure' => false];
        }
        if ($sveForLaterId) {
            $params['saveforlater_id'] = $saveForLaterId;
        }
        return $this->_getUrl('saveforlater/index/rss', $params);
    }

    /**
     * Retrieve default empty comment message
     *
     * @return \Magento\Framework\Phrase
     */
    public function defaultCommentString()
    {
        return __('Comment');
    }

    /**
     * Retrieve default empty comment message
     *
     * @return \Magento\Framework\Phrase
     */
    public function getDefaultSaveForLaterName()
    {
        return __('Save For Later');
    }

    /**
     * Calculate count of SaveForLater items and put value to customer session.
     *
     * Method called after SaveForLater modifications and trigger 'saveforlater_items_renewed' event.
     * Depends from configuration.
     *
     * @return $this
     */
    public function calculate()
    {
        $count = 0;
        if ($this->getCustomer()) {
            $collection = $this->getSaveForLaterItemCollection()->setInStockFilter(true);
            if ($this->scopeConfig->getValue(
                self::XML_PATH_SAVEFORLATER_LINK_USE_QTY,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            )
            ) {
                $count = $collection->getItemsQty();
            } else {
                $count = $collection->count();
            }
            $this->_customerSession->setSaveForLaterDisplayType(
                $this->scopeConfig->getValue(
                    self::XML_PATH_SAVEFORLATER_LINK_USE_QTY,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                )
            );
            $this->_customerSession->setDisplayOutOfStockProducts(
                $this->scopeConfig->getValue(
                    self::XML_PATH_CATALOGINVENTORY_SHOW_OUT_OF_STOCK,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                )
            );
        }
        $this->_customerSession->setSaveForLaterItemCount($count);
        $this->_eventManager->dispatch('saveforlater_items_renewed');
        return $this;
    }

    /**
     * Should display item quantities in my SaveForLater link
     *
     * @return bool
     */
    public function isDisplayQty()
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_SAVEFORLATER_LINK_USE_QTY,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Retrieve URL to item Product
     *
     * @param  \Mdbhojwani\SaveForLater\Model\Item|\Magento\Catalog\Model\Product $item
     * @param  array $additional
     * @return string
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getProductUrl($item, $additional = [])
    {
        if ($item instanceof \Magento\Catalog\Model\Product) {
            $product = $item;
        } else {
            $product = $item->getProduct();
        }

        $query = $this->getItemQueryOptions($item);
        if (isset($query['product'])) {
            $product = $query['product'];
        }

        $url = $product->getUrlModel()->getUrl($product, $query['additional'] ?? []);
        if (isset($query['fragment']) && count($query['fragment'])) {
            $url .= '#' . http_build_query($query['fragment']);
        }

        return $url;
    }

    /**
     * Generate query params from product options
     *
     * @param Item|Product $item
     * @return array
     * @throws NoSuchEntityException
     */
    private function getItemQueryOptions(Item|Product $item): array
    {
        $query = [];
        $buyRequest = $item->getBuyRequest();
        if (is_object($buyRequest)) {
            $config = $buyRequest->getSuperProductConfig();
            if ($config && !empty($config['product_id'])) {
                $product = $this->productRepository->getById(
                    $config['product_id'],
                    false,
                    $this->_storeManager->getStore()->getStoreId()
                );
                $query['product'] = $product;
            }
            $query['fragment'] = $this->getFragmentByProductType($buyRequest);
            if ($buyRequest->getQty()) {
                $query['additional']['_query']['qty'] = $buyRequest->getQty();
            }
        }
        return $query;
    }

    /**
     * Get product url with options and qty for complex products
     *
     * @param DataObject $buyRequest
     * @return array
     */
    private function getFragmentByProductType(DataObject $buyRequest): array
    {
        $fragment = $buyRequest->getSuperAttribute() ?? $buyRequest->getSuperGroup() ?? [];
        if ($buyRequest->getBundleOption()) {
            $fragment['bundle_option'] = $buyRequest->getBundleOption() ?? [];
            $fragment['bundle_option_qty'] = $buyRequest->getBundleOptionQty() ?? [];
        }
        return $fragment;
    }
}
