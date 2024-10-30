<?php

declare(strict_types=1);

namespace CrayssnLabsVgWort\Framework\Event\Extension;

/**
 * Trait SendJsonResponse
 *
 * @package   CrayssnLabsVgWort\Framework\Event\Extension
 *
 * @author    Sebastian Ludwig <dev@cl.team>
 * @copyright Copyright (c) 2023, CrayssnLabs Ludwig Wiegler GbR
 */
trait SendJsonResponse
{
    /**
     * Function sendJsonResponse
     *
     * @param array $_responseData
     */
    protected function sendJsonResponse(array $_responseData): void
    {
        header('Content-Type: application/json; charset=utf-8');

        echo json_encode($_responseData);

        exit();
    }
}
