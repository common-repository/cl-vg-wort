<?php

declare(strict_types=1);

namespace CrayssnLabsVgWort\Framework\VgWort\Rest\Request;

use CrayssnLabsVgWort\Framework\VgWort\Rest;

/**
 * Class PixelOverview
 *
 * @package   CrayssnLabsVgWort\Framework\VgWort\Rest\Request
 *
 * @author    Sebastian Ludwig <dev@cl.team>
 * @copyright Copyright (c) 2023, CrayssnLabs Ludwig Wiegler GbR
 */
class PixelOverview extends Rest\Request
{
    const ENDPOINT = 'https://tom.vgwort.de/api/cms/metis/rest/pixel/v1.0/overview';

    /**
     * @var array
     */
    private array $publicPixelIds;

    /**
     * @param array $_publicPixelIds
     */
    public function __construct(array $_publicPixelIds)
    {
        $this->publicPixelIds = $_publicPixelIds;
    }

    /**
     * Function data
     *
     * @return int[]
     */
    public function data(): array
    {
        return [
            'publicUIDs' => $this->publicPixelIds
        ];
    }
}
