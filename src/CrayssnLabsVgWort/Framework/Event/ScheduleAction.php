<?php

declare(strict_types=1);

namespace CrayssnLabsVgWort\Framework\Event;

use CrayssnLabsVgWort\Framework;
use CrayssnLabsVgWort\Framework\Plugin;

/**
 * Class ScheduleAction
 *
 * @package   CrayssnLabsVgWort\Framework\Event
 *
 * @author    Sebastian Ludwig <dev@cl.team>
 * @copyright Copyright (c) 2024, CrayssnLabs Ludwig Wiegler GbR
 */
abstract class ScheduleAction extends Framework\Event\Action
{
    protected const
        RECURRENCE_HOURLY = 'hourly',
        RECURRENCE_TWICE_DAILY = 'twicedaily',
        RECURRENCE_DAILY = 'daily',
        RECURRENCE_WEEKLY = 'weekly';

    protected const RECURRENCE = self::RECURRENCE_HOURLY;

    /**
     * Function remove
     *
     */
    public static function remove(): void
    {
        wp_clear_scheduled_hook( self::getIdentifier() );
    }

    /**
     * Function register
     *
     */
    public static function register(): void
    {
        if (! wp_next_scheduled ( self::getIdentifier() )) {
            wp_schedule_event( time(), self::RECURRENCE, self::getIdentifier() );
        }
    }
}
