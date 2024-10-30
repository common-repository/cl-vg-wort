<?php

declare(strict_types=1);

namespace CrayssnLabsVgWort\Action;

use CrayssnLabsVgWort\CrayssnLabsVgWort;
use CrayssnLabsVgWort\Framework\Event\Action;

/**
 * Class PersonalOptionsUpdate
 *
 * @package   CrayssnLabsVgWort\Action
 *
 * @author    Sebastian Ludwig <dev@cl.team>
 * @copyright Copyright (c) 2024, CrayssnLabs Ludwig Wiegler GbR
 */
class PersonalOptionsUpdate extends Action
{
    protected int $priority = 10;

    /**
     * Function process
     *
     * @param ...$parameters
     */
    public function process(...$parameters): void
    {
        $userId = (int)$parameters[0];

        if (!current_user_can('edit_user', $userId)) {
            return;
        }

        update_user_meta($userId, CrayssnLabsVgWort::CARD_NUMBER_INDEX, $_REQUEST[CrayssnLabsVgWort::CARD_NUMBER_INDEX]);
    }
}
