<?php

declare(strict_types=1);

namespace CrayssnLabsVgWort\Framework\VgWort\Rest;

/**
 * Class Request
 *
 * @package   CrayssnLabsVgWort\Framework\VgWort\Rest
 *
 * @author    Sebastian Ludwig <dev@cl.team>
 * @copyright Copyright (c) 2023, CrayssnLabs Ludwig Wiegler GbR
 */
abstract class Request
{
    const ENDPOINT = '';

    /**
     * Function data
     *
     * @return array
     */
    public function data(): array
    {
        return [];
    }

    /**
     * Function handleResponse
     *
     * @param array $_response
     *
     * @return array
     */
    public function handleResponse(array $_response): array
    {
        return $_response;
    }
}
