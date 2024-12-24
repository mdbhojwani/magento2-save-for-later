<?php
/**
 * @category Mdbhojwani
 * @package Mdbhojwani_SaveForLater
 * @author Manish Bhojwani <manishbhojwani3@gmail.com>
 * @github https://github.com/mdbhojwani
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
declare(strict_types = 1);

namespace Mdbhojwani\SaveForLater\Controller;

/**
 * Interface \Mdbhojwani\SaveForLater\Controller\SaveForLaterProviderInterface
 */
interface SaveForLaterProviderInterface
{
    /**
     * Retrieve SaveForLater
     *
     * @param string $saveForLaterId
     * @return \Mdbhojwani\SaveForLater\Model\SaveForLater
     */
    public function getSaveForLater($saveForLaterId = null);
}
