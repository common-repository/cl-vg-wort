<?php

declare(strict_types=1);

namespace CrayssnLabsVgWort\Action;

use CrayssnLabsVgWort\Framework\Event;
use CrayssnLabsVgWort\CrayssnLabsVgWort;

/**
 * Class VgWortReportTexts
 *
 * @package   CrayssnLabsVgWort\ScheduleEvent
 *
 * @author    Sebastian Ludwig <dev@cl.team>
 * @copyright Copyright (c) 2024, CrayssnLabs Ludwig Wiegler GbR
 *
 * @property \CrayssnLabsVgWort\CrayssnLabsVgWort $pluginInstance
 */
class VgWortReportTexts extends Event\ScheduleAction
{
    protected const RECURRENCE = self::RECURRENCE_HOURLY;

    /**
     * Function process
     *
     * @param ...$parameters
     *
     * @throws \Exception
     */
    public function process(...$parameters): void
    {
        if($this->pluginInstance->options['enableScheduledTask'] === CrayssnLabsVgWort::SCHEDULED_TASK_ENABLE)
        {
            $results = [];

            foreach ($this->pluginInstance->reportTextsToVgWort() as $identifier => $result)
            {
                $results[$identifier] = $result ? 'reported' : 'not-reported';
            }

            var_dump(array_count_values($results));
        }
    }
}
