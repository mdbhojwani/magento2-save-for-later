<?php
/**
 * @category Mdbhojwani
 * @package Mdbhojwani_SaveForLater
 * @author Manish Bhojwani <manishbhojwani3@gmail.com>
 * @github https://github.com/mdbhojwani
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
declare(strict_types = 1);

namespace Mdbhojwani\SaveForLater\Model\SaveForLater\BuyRequest;

use Mdbhojwani\SaveForLater\Model\SaveForLater\Data\SaveForLaterItem;

/**
 * Build buy request for adding products to SaveForLater
 */
interface BuyRequestDataProviderInterface
{
    /**
     * Provide buy request data from add to SaveForLater item request
     *
     * @param SaveForLaterItem $saveForLaterItemData
     * @param int|null $productId
     *
     * @return array
     */
    public function execute(SaveForLaterItem $saveForLaterItemData, ?int $productId): array;
}
