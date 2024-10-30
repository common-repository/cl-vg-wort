<?php

declare(strict_types=1);

namespace CrayssnLabsVgWort\Action;

use CrayssnLabsVgWort\CrayssnLabsVgWort;
use CrayssnLabsVgWort\Framework\Event\Action;
use CrayssnLabsVgWort\Framework\Event\Extension\SendJsonResponse;
use Exception;

/**
 * Class WpAjaxNoprivVgWortOrderCounter
 *
 * @package   CrayssnLabsVgWort\Action
 *
 * @author    Sebastian Ludwig <dev@cl.team>
 * @copyright Copyright (c) 2023, CrayssnLabs Ludwig Wiegler GbR
 */
class WpAjaxNoprivVgWortOrderCounter extends Action
{
    use SendJsonResponse;

    /**
     * Function action
     *
     */
    public function process(...$parameters): void
    {
        $contentLength = (int)$_POST['content-length'];
        $postId = (int)$_POST['post-id'];

        if ($contentLength >= CrayssnLabsVgWort::MIN_CONTENT_LENGTH) {
            try {
                $pixel = CrayssnLabsVgWort::init()->getVgWortPixelByIdentifier('post-' . $postId, true);

                $this->sendJsonResponse([
                    'status' => 'success',
                    'counter' => $pixel->getCounterUrl(),
                ]);
            } catch (Exception $e) {
                $this->sendJsonResponse([
                    'status' => 'failed',
                    'error' => $e->getMessage()
                ]);
            }
        }
    }
}
