<?php

declare(strict_types=1);

namespace CrayssnLabsVgWort\Action;

use CrayssnLabsVgWort\Framework\Event\Action;
use CrayssnLabsVgWort\CrayssnLabsVgWort;
use WP_User;

/**
 * Class EditUserProfile
 *
 * @package   CrayssnLabsVgWort\Action
 *
 * @author    Sebastian Ludwig <dev@cl.team>
 * @copyright Copyright (c) 2024, CrayssnLabs Ludwig Wiegler GbR
 */
class EditUserProfile extends Action
{
    protected int $priority = 10;

    /**
     * Function process
     *
     * @param ...$parameters
     */
    public function process(...$parameters): void
    {
        /**
         * @var WP_User $user
         */
        $user = $parameters[0];

        $cardNumber = get_user_meta( $user->ID, CrayssnLabsVgWort::CARD_NUMBER_INDEX, true);

        if(!is_string($cardNumber))
        {
            $cardNumber = (string)$cardNumber;
        }

        $index = CrayssnLabsVgWort::CARD_NUMBER_INDEX;

        echo <<<HTML
        <h2>VG Wort</h2>
        <table class="form-table">
            <tr>
                <th>
                    <label for="$index">Card number <br>(only for external authors)</label>
                </th>
                <td>
                    <input placeholder="e.g. 12345678" type="text" name="$index" id="$index" value="$cardNumber" class="regular-text" /><br>
                    <span class="description">This is the individual card number of the VG-Wort author. It is used to report texts to VG-Wort.<br><strong>Without this information, the texts are attributed to the main account.</strong></span>
                </td>
            </tr>
        </table>
        HTML;

    }
}
