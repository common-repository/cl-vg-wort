<?php

declare(strict_types=1);

namespace CrayssnLabsVgWort\Filter;

use CrayssnLabsVgWort\Framework\Event\Filter;

/**
 * Class UpdateOption
 *
 * @package   CrayssnLabsVgWort\Action
 *
 * @author    Sebastian Ludwig <dev@cl.team>
 * @copyright Copyright (c) 2023, CrayssnLabs Ludwig Wiegler GbR
 */
class PreUpdateOption extends Filter
{
    protected int $acceptedArgs = 3;

    public function process(...$parameters)
    {
        [$newValue, $identifier, $oldValue] = $parameters;

        if($identifier === $this->pluginInstance->identifier)
        {
            if(!isset($newValue['counters']) && isset($oldValue['counters']))
            {
                $newValue['counters'] = $oldValue['counters'];
            }
        }

        return $newValue;
    }
}