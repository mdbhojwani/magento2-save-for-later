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

use Mdbhojwani\SaveForLater\Model\SaveForLater;

/**
 * DTO represent output for \Mdbhojwani\SaveForLaterGraphQl\Model\Resolver\AddProductsToSaveForLaterResolver
 */
class SaveForLaterOutput
{
    /**
     * @var SaveForLater
     */
    private $saveForLater;

    /**
     * @var Error[]
     */
    private $errors;

    /**
     * @param SaveForLater $saveForLater
     * @param Error[] $errors
     */
    public function __construct(SaveForLater $saveForLater, array $errors)
    {
        $this->saveForLater = $saveForLater;
        $this->errors = $errors;
    }

    /**
     * Get SaveForLater
     *
     * @return SaveForLater
     */
    public function getSaveForLater(): SaveForLater
    {
        return $this->saveForLater;
    }

    /**
     * Get errors happened during adding products to SaveForLater
     *
     * @return Error[]
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
