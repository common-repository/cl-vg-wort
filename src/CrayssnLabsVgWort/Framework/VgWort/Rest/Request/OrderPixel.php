<?php

declare(strict_types=1);

namespace CrayssnLabsVgWort\Framework\VgWort\Rest\Request;

use CrayssnLabsVgWort\Framework\VgWort\Rest;

/**
 * Class OrderPixel
 *
 * @package   CrayssnLabsVgWort\Framework\VgWort\Rest\Request
 *
 * @author    Sebastian Ludwig <dev@cl.team>
 * @copyright Copyright (c) 2023, CrayssnLabs Ludwig Wiegler GbR
 */
class OrderPixel extends Rest\Request
{
    const ENDPOINT = 'https://tom.vgwort.de/api/cms/metis/rest/pixel/v1.0/order';

    /**
     * @var int
     */
    private int $count;

    /**
     * @param int $_count
     */
    public function __construct(int $_count = 1)
    {
        $this->count = $_count;
    }

    /**
     * Function data
     *
     * @return int[]
     */
    public function data(): array
    {
        return [
            'count' => $this->count
        ];
    }
}
