<?php
/**
 * @category Mdbhojwani
 * @package Mdbhojwani_SaveForLater
 * @author Manish Bhojwani <manishbhojwani3@gmail.com>
 * @github https://github.com/mdbhojwani
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
declare(strict_types = 1);

namespace Mdbhojwani\SaveForLater\Model\Adminhtml\ResourceModel\Item\Product;

use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Mdbhojwani\SaveForLater\Model\ResourceModel\Item\Collection as SaveForLaterItemCollection;
use Mdbhojwani\SaveForLater\Model\ResourceModel\Item\Product\CollectionBuilderInterface;

/**
 * SaveForLater items products collection builder for adminhtml area
 */
class CollectionBuilder implements CollectionBuilderInterface
{
    /**
     * @inheritDoc
     */
    public function build(SaveForLaterItemCollection $saveForLaterItemCollection, Collection $productCollection): Collection
    {
        return $productCollection;
    }
}
