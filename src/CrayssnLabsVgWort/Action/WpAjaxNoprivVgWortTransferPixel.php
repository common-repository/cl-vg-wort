<?php

declare(strict_types=1);

namespace CrayssnLabsVgWort\Action;

use CrayssnLabsVgWort\CrayssnLabsVgWort;
use CrayssnLabsVgWort\Framework\Event\Action;
use CrayssnLabsVgWort\Framework\Event\Extension\SendJsonResponse;
use CrayssnLabsVgWort\Framework\VgWort\Rest\Client;
use CrayssnLabsVgWort\Framework\VgWort\Rest\Request;

/**
 * Class WpAjaxNoprivVgWortTransferPixel
 *
 * @package   CrayssnLabsVgWort\Action
 *
 * @author    Sebastian Ludwig <dev@cl.team>
 * @copyright Copyright (c) 2023, CrayssnLabs Ludwig Wiegler GbR
 */
class WpAjaxNoprivVgWortTransferPixel extends Action
{
    use SendJsonResponse;

    /**
     * Function action
     *
     * @throws \Exception
     */
    public function process(...$parameters): void
    {
        $publicIdentifier = sanitize_text_field($_POST['public-identifier']);
        $domain = sanitize_text_field($_POST['domain']);
        $postId = (int)$_POST['post-id'];

        CrayssnLabsVgWort::init()->setVgWortPixelDataByIdentifier('post-' . $postId, $this->enrich([
            'publicIdentifier' => $publicIdentifier,
            'domain' => $domain
        ]));

        $this->sendJsonResponse([
            'status' => 'success',
            'data' => [
                'postId' => $postId,
                'publicIdentifier' => $publicIdentifier,
            ]
        ]);
    }

    /**
     * Function enrich
     *
     * @param array $_pixelData
     *
     * @return array
     * @throws \Exception
     */
    private function enrich(array $_pixelData): array
    {
        $globalApiKey = CrayssnLabsVgWort::init()->options['apiKey'];

        $client = new Client($globalApiKey);

        $result = $client->sendRequest(new Request\PixelOverview([$_pixelData['publicIdentifier']]));

        if ($result['status'] === Client::RESULT_STATUS_NOT_FOUND) {
            return $_pixelData;
        }

        $pixelData = array_pop($result['pixels']);

        $_pixelData['privateIdentifier'] = $pixelData['privateUID'];
        $_pixelData['apiKey'] = $globalApiKey;

        return $_pixelData;
    }
}
