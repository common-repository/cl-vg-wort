<?php

declare(strict_types=1);

namespace CrayssnLabsVgWort\Framework\VgWort;

use CrayssnLabsVgWort\CrayssnLabsVgWort;

/**
 * Class PixelCollection
 *
 * @package   CrayssnLabsVgWort\Framework\VgWort
 *
 * @author    Sebastian Ludwig <dev@cl.team>
 * @copyright Copyright (c) 2023, CrayssnLabs Ludwig Wiegler GbR
 */
class PixelCollection
{
    /**
     * @var array
     */
    private array $pixelData;

    /**
     *
     */
    public function __construct()
    {
        $this->pixelData = CrayssnLabsVgWort::init()->options['counters'];
    }

    /**
     * Function collect
     *
     * @param int $_offset
     * @param int $_limit
     *
     * @return array
     */
    public function collect(int $_offset = 0, int $_limit = 10): array
    {
        $pixels = [];

        foreach ($this->pixelData as $pixelData) {
            $pixels[] = Pixel::getInstanceByArray($pixelData);
        }

        return $pixels;
    }
}
