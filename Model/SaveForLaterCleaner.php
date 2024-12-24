<?php
/**
 * @category Mdbhojwani
 * @package Mdbhojwani_SaveForLater
 * @author Manish Bhojwani <manishbhojwani3@gmail.com>
 * @github https://github.com/mdbhojwani
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
declare(strict_types = 1);

namespace Mdbhojwani\SaveForLater\Model;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Exception\LocalizedException;
use Mdbhojwani\SaveForLater\Model\ResourceModel\Item as ItemResourceModel;
use Mdbhojwani\SaveForLater\Model\ResourceModel\Item\Option as ItemOptionResourceModel;

/**
 * Deletes SaveForLater items
 */
class SaveForLaterCleaner
{
    /**
     * SaveForLater Item Option resource model
     *
     * @var ItemOptionResourceModel
     */
    private $itemOptionResourceModel;

    /**
     * SaveForLater Item Option resource model
     *
     * @var ItemResourceModel
     */
    private $itemResourceModel;

    /**
     * @param ItemOptionResourceModel $itemOptionResourceModel
     * @param ItemResourceModel $itemResourceModel
     */
    public function __construct(
        ItemOptionResourceModel $itemOptionResourceModel,
        ItemResourceModel $itemResourceModel
    ) {
        $this->itemOptionResourceModel = $itemOptionResourceModel;
        $this->itemResourceModel = $itemResourceModel;
    }

    /**
     * Deletes all SaveForLater items related the specified product
     *
     * @param ProductInterface $product
     * @throws LocalizedException
     */
    public function execute(ProductInterface $product)
    {
        $connection = $this->itemResourceModel->getConnection();

        $selectQuery = $connection
            ->select()
            ->from(['w_item' => $this->itemResourceModel->getMainTable()])
            ->join(
                ['w_item_option' => $this->itemOptionResourceModel->getMainTable()],
                'w_item.saveforlater_item_id = w_item_option.saveforlater_item_id'
            )
            ->where('w_item_option.product_id = ?', $product->getId());

        $connection->query($selectQuery->deleteFromSelect('w_item'));
    }
}
