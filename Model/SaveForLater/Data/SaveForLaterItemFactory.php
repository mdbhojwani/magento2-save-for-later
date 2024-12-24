<?php
/**
 * @category Mdbhojwani
 * @package Mdbhojwani_SaveForLater
 * @author Manish Bhojwani <manishbhojwani3@gmail.com>
 * @github https://github.com/mdbhojwani
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
declare(strict_types = 1);

namespace Mdbhojwani\SaveForLater\Model\SaveForLater\Data;

use Magento\Framework\Exception\InputException;

/**
 * Create SaveForLaterItem DTO
 */
class SaveForLaterItemFactory
{
    /**
     * Create SaveForLater item DTO
     *
     * @param array $data
     *
     * @return SaveForLaterItem
     */
    public function create(array $data): SaveForLaterItem
    {
        return new SaveForLaterItem(
            $data['quantity'] ?? 0,
            $data['sku'] ?? null,
            $data['parent_sku'] ?? null,
            isset($data['saveforlater_item_id']) ? (int) $data['saveforlater_item_id'] : null,
            $data['description'] ?? null,
            isset($data['selected_options']) ? $this->createSelectedOptions($data['selected_options']) : [],
            isset($data['entered_options']) ? $this->createEnteredOptions($data['entered_options']) : []
        );
    }

    /**
     * Create array of Entered Options
     *
     * @param array $options
     *
     * @return EnteredOption[]
     */
    private function createEnteredOptions(array $options): array
    {
        return \array_map(
            function (array $option) {
                if (!isset($option['uid'], $option['value'])) {
                    throw new InputException(
                        __('Required fields are not present EnteredOption.uid, EnteredOption.value')
                    );
                }
                return new EnteredOption($option['uid'], $option['value']);
            },
            $options
        );
    }

    /**
     * Create array of Selected Options
     *
     * @param string[] $options
     *
     * @return SelectedOption[]
     */
    private function createSelectedOptions(array $options): array
    {
        return \array_map(
            function ($option) {
                return new SelectedOption($option);
            },
            $options
        );
    }
}
