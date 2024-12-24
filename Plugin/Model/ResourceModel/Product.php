<?php
/**
 * @category Mdbhojwani
 * @package Mdbhojwani_SaveForLater
 * @author Manish Bhojwani <manishbhojwani3@gmail.com>
 * @github https://github.com/mdbhojwani
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
declare(strict_types = 1);

namespace Mdbhojwani\SaveForLater\Plugin\Model\ResourceModel;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ResourceModel\Product as ProductResourceModel;
use Magento\Framework\Exception\LocalizedException;
use Mdbhojwani\SaveForLater\Model\SaveForLaterCleaner;

/**
 * Cleans up SaveForLater items referencing the product being deleted
 */
class Product
{
    /**
     * @var SaveForLaterCleaner
     */
    private $saveForLaterCleaner;

    /**
     * @param SaveForLaterCleaner $saveForLaterCleaner
     */
    public function __construct(
        SaveForLaterCleaner $saveForLaterCleaner
    ) {
        $this->saveForLaterCleaner = $saveForLaterCleaner;
    }

    /**
     * Cleans up SaveForLater items referencing the product being deleted
     *
     * @param ProductResourceModel $productResourceModel
     * @param mixed $product
     * @return void
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeDelete(
        ProductResourceModel $productResourceModel,
        $product
    ) {
        if ($product instanceof ProductInterface) {
            $this->saveForLaterCleaner->execute($product);
        }
    }
}
