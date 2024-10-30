<?php

declare(strict_types=1);

namespace CrayssnLabsVgWort\Action;

use CrayssnLabsVgWort\CrayssnLabsVgWort;
use CrayssnLabsVgWort\Framework\Event\Action;
use CrayssnLabsVgWort\Framework\VgWort\Pixel;

/**
 * Class WpFooter
 *
 * @package   CrayssnLabs\Action
 *
 * @author    Sebastian Ludwig <dev@cl.team>
 * @copyright Copyright (c) 2023, CrayssnLabs Ludwig Wiegler GbR
 */
class WpFooter extends Action
{
    /**
     * Function action
     *
     * @throws \Exception
     */
    public function process(...$parameters): void
    {
        $postId = get_the_ID();

        if ($postId === false) {
            return;
        }

        $pixel = CrayssnLabsVgWort::init()->getVgWortPixelByIdentifier('post-' . $postId);

        if ($pixel === null || isset($_GET['verifyPixel']))
        {
            $this->processPostId($postId);

            return ;
        }

        $this->processPixel($pixel);
    }

    /**
     * Function processNoPixelFound
     *
     */
    private function processPostId(int $_postId): void
    {
        $options = [
            'ajaxRequestUrl' => admin_url("admin-ajax.php"),
            'minContentLength' => CrayssnLabsVgWort::MIN_CONTENT_LENGTH,
            'postId' => $_postId
        ];

        $scriptClassContent = file_get_contents(__DIR__ . '/../_resources/VgWort.js');
        $options = json_encode($options);

        echo "<script id=\"cl-vg-wort\">$scriptClassContent (new VgWort($options)).integrateCounter();</script>";
    }

    /**
     * Function processPixel
     *
     * @param \CrayssnLabsVgWort\Framework\VgWort\Pixel $_pixel
     */
    private function processPixel(Pixel $_pixel): void
    {
        $pixelUrl = esc_url($_pixel->getCounterUrl());

        echo <<<HTML
        <script id="cl-vg-wort">
            setTimeout(()=>{if(null===document.querySelector("img[src*='vgwort.de']")){let e=document.createElement("img");e.alt="VG Wort Counter";e.width=1;e.height=1;e.src="$pixelUrl";document.body.appendChild(e)}},500);
        </script>
        HTML;
    }
}
