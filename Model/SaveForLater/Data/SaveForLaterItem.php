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

/**
 * DTO represents SaveForLater Item data
 */
class SaveForLaterItem
{
    /**
     * @var float
     */
    private $quantity;

    /**
     * @var string|null
     */
    private $sku;

    /**
     * @var string
     */
    private $parentSku;

    /**
     * @var int|null
     */
    private $id;

    /**
     * @var string|null
     */
    private $description;

    /**
     * @var SelectedOption[]
     */
    private $selectedOptions;

    /**
     * @var EnteredOption[]
     */
    private $enteredOptions;

    /**
     * @param float $quantity
     * @param string|null $sku
     * @param string|null $parentSku
     * @param int|null $id
     * @param string|null $description
     * @param array|null $selectedOptions
     * @param array|null $enteredOptions
     */
    public function __construct(
        float $quantity,
        string $sku = null,
        string $parentSku = null,
        int $id = null,
        string $description = null,
        array $selectedOptions = null,
        array $enteredOptions = null
    ) {
        $this->quantity = $quantity;
        $this->sku = $sku;
        $this->parentSku = $parentSku;
        $this->id = $id;
        $this->description = $description;
        $this->selectedOptions = $selectedOptions;
        $this->enteredOptions = $enteredOptions;
    }

    /**
     * Get SaveForLater item id
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Get SaveForLater item description
     *
     * @return string|null
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Get sku
     *
     * @return string|null
     */
    public function getSku(): ?string
    {
        return $this->sku;
    }

    /**
     * Get quantity
     *
     * @return float
     */
    public function getQuantity(): float
    {
        return $this->quantity;
    }

    /**
     * Get parent sku
     *
     * @return string|null
     */
    public function getParentSku(): ?string
    {
        return $this->parentSku;
    }

    /**
     * Get selected options
     *
     * @return SelectedOption[]|null
     */
    public function getSelectedOptions(): ?array
    {
        return $this->selectedOptions;
    }

    /**
     * Get entered options
     *
     * @return EnteredOption[]|null
     */
    public function getEnteredOptions(): ?array
    {
        return $this->enteredOptions;
    }
}
