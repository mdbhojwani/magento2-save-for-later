<?php
/**
 * @category Mdbhojwani
 * @package Mdbhojwani_SaveForLater
 * @author Manish Bhojwani <manishbhojwani3@gmail.com>
 * @github https://github.com/mdbhojwani
 * @license http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */
declare(strict_types = 1);

namespace Mdbhojwani\SaveForLater\Block\Rss;

/**
 * SaveForLater RSS URL to Email Block
 */
class EmailLink extends Link
{
    /**
     * @var string
     */
    protected $_template = 'Mdbhojwani_SaveForLater::rss/email.phtml';

    /**
     * @return array
     */
    protected function getLinkParams()
    {
        $params = parent::getLinkParams();
        $saveForLater = $this->saveForLaterHelper->getSaveForLater();
        $sharingCode = $saveForLater->getSharingCode();
        if ($sharingCode) {
            $params['sharing_code'] = $sharingCode;
        }
        return $params;
    }
}
