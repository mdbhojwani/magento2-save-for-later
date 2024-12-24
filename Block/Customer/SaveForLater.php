<?php
/**
 * @category Mdbhojwani
 * @package Mdbhojwani_SaveForLater
 * @author Manish Bhojwani <manishbhojwani3@gmail.com>
 * @github https://github.com/mdbhojwani
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
declare(strict_types = 1);

namespace Mdbhojwani\SaveForLater\Block\Customer;

use Mdbhojwani\SaveForLater\Helper\Data as SaveForLaterHelper;

/**
 * SaveForLater block customer items.
 */
class SaveForLater extends \Mdbhojwani\SaveForLater\Block\AbstractBlock
{
    /**
     * List of product options rendering configurations by product type
     *
     * @var array
     */
    protected $_optionsCfg = [];

    /**
     * @var \Magento\Catalog\Helper\Product\ConfigurationPool
     */
    protected $_helperPool;

    /**
     * @var  \Mdbhojwani\SaveForLater\Model\ResourceModel\Item\Collection
     * @since 101.1.1
     */
    protected $_collection;

    /**
     * @var \Magento\Customer\Helper\Session\CurrentCustomer
     */
    protected $currentCustomer;

    /**
     * @var \Magento\Framework\Data\Helper\PostHelper
     */
    protected $postDataHelper;

    /**
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param SaveForLaterHelper $saveForLaterHelper
     * @param \Magento\Catalog\Helper\Product\ConfigurationPool $helperPool
     * @param \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer
     * @param \Magento\Framework\Data\Helper\PostHelper $postDataHelper
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Framework\App\Http\Context $httpContext,
        SaveForLaterHelper $saveForLaterHelper,
        \Magento\Catalog\Helper\Product\ConfigurationPool $helperPool,
        \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $httpContext,
            $saveForLaterHelper,
            $data
        );
        $this->_helperPool = $helperPool;
        $this->currentCustomer = $currentCustomer;
        $this->postDataHelper = $postDataHelper;
    }

    /**
     * Add SaveForLater conditions to collection
     *
     * @param  \Mdbhojwani\SaveForLater\Model\ResourceModel\Item\Collection $collection
     * @return $this
     */
    protected function _prepareCollection($collection)
    {
        $collection->setInStockFilter(true)->setOrder('added_at', 'ASC');
        return $this;
    }

    /**
     * Paginate SaveForLater Product Items collection
     *
     * @return void
     * @SuppressWarnings(PHPMD.RequestAwareBlockMethod)
     */
    private function paginateCollection()
    {
        $page = $this->getRequest()->getParam("p", 1);
        $limit = $this->getRequest()->getParam("limit", 10);
        $this->_collection
            ->setPageSize($limit)
            ->setCurPage($page);
    }

    /**
     * Retrieve SaveForLater Product Items collection
     *
     * @return \Mdbhojwani\SaveForLater\Model\ResourceModel\Item\Collection
     * @since 101.1.1
     */
    public function getSaveForLaterItems()
    {
        if ($this->_collection === null) {
            $this->_collection = $this->_createSaveForLaterItemCollection();
            $this->_prepareCollection($this->_collection);
            $this->paginateCollection();
        }
        return $this->_collection;
    }

    /**
     * Preparing global layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        $this->pageConfig->getTitle()->set(__('My Save For Later List'));
        $this->getChildBlock('saveforlater_item_pager')
            ->setUseContainer(
                true
            )->setShowAmounts(
                true
            )->setFrameLength(
                $this->_scopeConfig->getValue(
                    'design/pagination/pagination_frame',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                )
            )->setJump(
                $this->_scopeConfig->getValue(
                    'design/pagination/pagination_frame_skip',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                )
            )->setLimit(
                $this->getLimit()
            )
            ->setCollection($this->getSaveForLaterItems());
        return $this;
    }

    /**
     * Retrieve Back URL
     *
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl('customer/account/');
    }

    /**
     * Sets all options render configurations
     *
     * @param null|array $optionCfg
     * @return $this
     */
    public function setOptionsRenderCfgs($optionCfg)
    {
        $this->_optionsCfg = $optionCfg;
        return $this;
    }

    /**
     * Returns all options render configurations
     *
     * @return array
     */
    public function getOptionsRenderCfgs()
    {
        return $this->_optionsCfg;
    }

    /**
     * Adds config for rendering product type options
     *
     * @param string $productType
     * @param string $helperName
     * @param null|string $template
     * @return $this
     */
    public function addOptionsRenderCfg($productType, $helperName, $template = null)
    {
        $this->_optionsCfg[$productType] = ['helper' => $helperName, 'template' => $template];
        return $this;
    }

    /**
     * Returns html for showing item options
     *
     * @param string $productType
     * @return array|null
     */
    public function getOptionsRenderCfg($productType)
    {
        if (isset($this->_optionsCfg[$productType])) {
            return $this->_optionsCfg[$productType];
        } elseif (isset($this->_optionsCfg['default'])) {
            return $this->_optionsCfg['default'];
        } else {
            return null;
        }
    }

    /**
     * Returns html for showing item options
     *
     * @param \Mdbhojwani\SaveForLater\Model\Item $item
     * @return string
     */
    public function getDetailsHtml(\Mdbhojwani\SaveForLater\Model\Item $item)
    {
        $cfg = $this->getOptionsRenderCfg($item->getProduct()->getTypeId());
        if (!$cfg) {
            return '';
        }

        $block = $this->getChildBlock('item_options');
        if (!$block) {
            return '';
        }

        if ($cfg['template']) {
            $template = $cfg['template'];
        } else {
            $cfgDefault = $this->getOptionsRenderCfg('default');
            if (!$cfgDefault) {
                return '';
            }
            $template = $cfgDefault['template'];
        }

        $block->setTemplate($template);
        $block->setOptionList($this->_helperPool->get($cfg['helper'])->getOptions($item));
        return $block->toHtml();
    }

    /**
     * Returns qty to show visually to user
     *
     * @param \Mdbhojwani\SaveForLater\Model\Item $item
     * @return float
     */
    public function getAddToCartQty(\Mdbhojwani\SaveForLater\Model\Item $item)
    {
        $qty = $this->getQty($item);
        return $qty ? $qty : 1;
    }

    /**
     * Get add all to cart params for POST request
     *
     * @return string
     */
    public function getAddAllToCartParams()
    {
        return $this->postDataHelper->getPostData(
            $this->getUrl('saveforlater/index/allcart'),
            ['saveforlater_id' => $this->getSaveForLaterInstance()->getId()]
        );
    }

    /**
     * @inheritdoc
     */
    protected function _toHtml()
    {
        if ($this->currentCustomer->getCustomerId()) {
            return parent::_toHtml();
        } else {
            return '';
        }
    }
}
