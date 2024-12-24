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
 * Data provider for custom options buy requests
 */
class CustomizableOptionDataProvider implements BuyRequestDataProviderInterface
{
    private const PROVIDER_OPTION_TYPE = 'custom-option';

    /**
     * @inheritdoc
     *
     * @phpcs:disable Magento2.Functions.DiscouragedFunction
     */
    public function execute(SaveForLaterItem $saveForLaterItemData, ?int $productId): array
    {
        $customizableOptionsData = [];
        foreach ($saveForLaterItemData->getSelectedOptions() as $optionData) {
            $optionData = \explode('/', base64_decode($optionData->getId()));

            if ($this->isProviderApplicable($optionData) === false) {
                continue;
            }

            [$optionType, $optionId, $optionValue] = $optionData;

            if ($optionType == self::PROVIDER_OPTION_TYPE) {
                $customizableOptionsData[$optionId][] = $optionValue;
            }
        }

        foreach ($saveForLaterItemData->getEnteredOptions() as $option) {
            $optionData = \explode('/', base64_decode($option->getUid()));

            if ($this->isProviderApplicable($optionData) === false) {
                continue;
            }

            [$optionType, $optionId] = $optionData;

            if ($optionType == self::PROVIDER_OPTION_TYPE) {
                $customizableOptionsData[$optionId][] = $option->getValue();
            }
        }

        if (empty($customizableOptionsData)) {
            return $customizableOptionsData;
        }

        $result = ['options' => $this->flattenOptionValues($customizableOptionsData)];

        if ($productId) {
            $result += ['product' => $productId];
        }

        return $result;
    }

    /**
     * Flatten option values for non-multiselect customizable options
     *
     * @param array $customizableOptionsData
     *
     * @return array
     */
    private function flattenOptionValues(array $customizableOptionsData): array
    {
        foreach ($customizableOptionsData as $optionId => $optionValue) {
            if (count($optionValue) === 1) {
                $customizableOptionsData[$optionId] = $optionValue[0];
            }
        }

        return $customizableOptionsData;
    }

    /**
     * Checks whether this provider is applicable for the current option
     *
     * @param array $optionData
     * @return bool
     */
    private function isProviderApplicable(array $optionData): bool
    {
        return $optionData[0] === self::PROVIDER_OPTION_TYPE;
    }
}
