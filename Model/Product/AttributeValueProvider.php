<?php
/**
 * @category Mdbhojwani
 * @package Mdbhojwani_SaveForLater
 * @author Manish Bhojwani <manishbhojwani3@gmail.com>
 * @github https://github.com/mdbhojwani
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
declare(strict_types = 1);

namespace Mdbhojwani\SaveForLater\Model\Product;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;

/**
 * Provides existing attribute value for a product entity.
 */
class AttributeValueProvider
{
    /**
     * @var ProductCollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @param ProductCollectionFactory $productCollectionFactory
     */
    public function __construct(
        ProductCollectionFactory $productCollectionFactory
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
    }

    /**
     * Provides existing raw attribute value by the attribute code of the product entity.
     *
     * @param int $productId
     * @param string $attributeCode
     * @param int|null $storeId
     * @return null|string
     */
    public function getRawAttributeValue(int $productId, string $attributeCode, int $storeId = null):? string
    {
        $collection = $this->productCollectionFactory->create();
        $collection->addIdFilter($productId)
            ->addStoreFilter($storeId)
            ->addAttributeToSelect($attributeCode);

        if ($collection->isEnabledFlat()) {
            $data = $collection->getConnection()->fetchRow($collection->getSelect());
            $attributeValue = $data[$attributeCode] ?? null;
        } else {
            $attributeValue = $collection->getFirstItem()->getData($attributeCode);
        }

        return $attributeValue;
    }
}
