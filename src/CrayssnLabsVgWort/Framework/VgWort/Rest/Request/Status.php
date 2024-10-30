<?php

declare(strict_types=1);

namespace CrayssnLabsVgWort\Framework\VgWort\Rest\Request;

use CrayssnLabsVgWort\Framework\VgWort\Rest;

/**
 * Class Status
 *
 * @package   CrayssnLabsVgWort\Framework\VgWort\Rest\Request
 *
 * @author    Sebastian Ludwig <dev@cl.team>
 * @copyright Copyright (c) 2023, CrayssnLabs Ludwig Wiegler GbR
 */
class Status extends Rest\Request
{
    public const ENDPOINT = 'https://tom.vgwort.de/api/cms/status';
}
