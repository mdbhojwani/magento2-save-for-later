<?php
/**
 * @category Mdbhojwani
 * @package Mdbhojwani_SaveForLater
 * @author Manish Bhojwani <manishbhojwani3@gmail.com>
 * @github https://github.com/mdbhojwani
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
declare(strict_types = 1);

namespace Mdbhojwani\SaveForLater\Model\ResourceModel\Item\Collection;

use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Customer\Controller\RegistryConstants;
use Mdbhojwani\SaveForLater\Model\Item;

/**
 * SaveForLater item collection for grid grouped by customer id
 */
class Grid extends \Mdbhojwani\SaveForLater\Model\ResourceModel\Item\Collection
{
    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registryManager;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactory $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration
     * @param \Magento\Sales\Helper\Admin $adminhtmlSales
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Mdbhojwani\SaveForLater\Model\Config $saveForLaterConfig
     * @param \Magento\Catalog\Model\Product\Visibility $productVisibility
     * @param \Magento\Framework\App\ResourceConnection $coreResource
     * @param \Mdbhojwani\SaveForLater\Model\ResourceModel\Item\Option\CollectionFactory $optionCollectionFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Catalog\Model\ResourceModel\ConfigFactory $catalogConfFactory
     * @param \Magento\Catalog\Model\Entity\AttributeFactory $catalogAttrFactory
     * @param \Mdbhojwani\SaveForLater\Model\ResourceModel\Item $resource
     * @param \Magento\Framework\App\State $appState
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\DB\Adapter\AdapterInterface $connection
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\CatalogInventory\Api\StockConfigurationInterface $stockConfiguration,
        \Magento\Sales\Helper\Admin $adminhtmlSales,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Mdbhojwani\SaveForLater\Model\Config $saveForLaterConfig,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        \Magento\Framework\App\ResourceConnection $coreResource,
        \Mdbhojwani\SaveForLater\Model\ResourceModel\Item\Option\CollectionFactory $optionCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\ConfigFactory $catalogConfFactory,
        \Magento\Catalog\Model\Entity\AttributeFactory $catalogAttrFactory,
        \Mdbhojwani\SaveForLater\Model\ResourceModel\Item $resource,
        \Magento\Framework\App\State $appState,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null
    ) {
        $this->_registryManager = $registry;
        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $stockConfiguration,
            $adminhtmlSales,
            $storeManager,
            $date,
            $saveForLaterConfig,
            $productVisibility,
            $coreResource,
            $optionCollectionFactory,
            $productCollectionFactory,
            $catalogConfFactory,
            $catalogAttrFactory,
            $resource,
            $appState,
            $connection
        );
    }

    /**
     * @inheritdoc
     */
    protected function _initSelect()
    {
        parent::_initSelect();

        $customerId = $this->_registryManager->registry(RegistryConstants::CURRENT_CUSTOMER_ID);
        $this->addDaysInSaveForLater()
            ->addStoreData()
            ->addCustomerIdFilter($customerId)
            ->resetSortOrder();

        return $this;
    }

    /**
     * @inheritdoc
     */
    protected function _assignProducts()
    {
        /** @var ProductCollection $productCollection */
        $productCollection = $this->_productCollectionFactory->create()
            ->addAttributeToSelect($this->saveForLaterConfig->getProductAttributes())
            ->addIdFilter($this->_productIds);

        /** @var Item $item */
        foreach ($this as $item) {
            $product = $productCollection->getItemById($item->getProductId());
            if ($product) {
                $product->setCustomOptions([]);
                $item->setProduct($product);
                $item->setProductName($product->getName());
                $item->setName($product->getName());
                $item->setPrice($product->getPrice());
            }
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setOrder($field, $direction = self::SORT_ORDER_DESC)
    {
        if ($field == 'product_name') {
            return $this->setOrderByProductName($direction);
        } else {
            if ($field == 'days_in_saveforlater') {
                $field = 'added_at';
                $direction = $direction == self::SORT_ORDER_DESC ? self::SORT_ORDER_ASC : self::SORT_ORDER_DESC;
            }
            return parent::setOrder($field, $direction);
        }
    }

    /**
     * @inheritdoc
     */
    public function addFieldToFilter($field, $condition = null)
    {
        switch ($field) {
            case 'product_name':
                $value = (string)$condition['like'];
                $value = trim(trim($value, "'"), "%");
                return $this->addProductNameFilter($value);
            case 'store_id':
                if (isset($condition['eq'])) {
                    return $this->addStoreFilter($condition);
                }
                break;
            case 'days_in_saveforlater':
                if (!isset($condition['datetime'])) {
                    return $this->addDaysFilter($condition);
                }
                break;
            case 'qty':
                if (isset($condition['from']) || isset($condition['to'])) {
                    return $this->addQtyFilter($field, $condition);
                }
        }

        return parent::addFieldToFilter($field, $condition);
    }

    /**
     * Add quantity to filter
     *
     * @param string $field
     * @param array $condition
     * @return \Mdbhojwani\SaveForLater\Model\ResourceModel\Item\Collection
     */
    private function addQtyFilter(string $field, array $condition)
    {
        return parent::addFieldToFilter('main_table.' . $field, $condition);
    }
}
