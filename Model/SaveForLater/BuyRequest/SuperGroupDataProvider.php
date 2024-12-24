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
 * Data provider for grouped product buy requests
 */
class SuperGroupDataProvider implements BuyRequestDataProviderInterface
{
    private const PROVIDER_OPTION_TYPE = 'grouped';

    /**
     * @inheritdoc
     *
     * @phpcs:disable Magento2.Functions.DiscouragedFunction
     */
    public function execute(SaveForLaterItem $saveForLaterItemData, ?int $productId): array
    {
        $groupedData = [];

        foreach ($saveForLaterItemData->getSelectedOptions() as $optionData) {
            $optionData = \explode('/', base64_decode($optionData->getId()));

            if ($this->isProviderApplicable($optionData) === false) {
                continue;
            }

            [, $simpleProductId, $quantity] = $optionData;

            $groupedData[$simpleProductId] = $quantity;
        }

        if (empty($groupedData)) {
            return $groupedData;
        }

        $result = ['super_group' => $groupedData];

        if ($productId) {
            $result += ['product' => $productId];
        }

        return $result;
    }

    /**
     * Checks whether this provider is applicable for the current option
     *
     * @param array $optionData
     *
     * @return bool
     */
    private function isProviderApplicable(array $optionData): bool
    {
        return $optionData[0] === self::PROVIDER_OPTION_TYPE;
    }
}
