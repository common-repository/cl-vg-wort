<?php

declare(strict_types=1);

namespace CrayssnLabsVgWort\Framework\VgWort\Rest\Request;

use CrayssnLabsVgWort\Framework\VgWort\Rest;
use DateTime;

/**
 * Class ReportText
 *
 * @package   CrayssnLabsVgWort\Framework\VgWort\Rest\Request
 *
 * @author    Sebastian Ludwig <dev@cl.team>
 * @copyright Copyright (c) 2024, CrayssnLabs Ludwig Wiegler GbR
 */
class ReportText extends Rest\Request
{
    const ENDPOINT = 'https://tom.vgwort.de/api/cms/metis/rest/message/v1.0/save-message';

    /**
     * @var string
     */
    private string $privateIdentifier;

    /**
     * @var string
     */
    private string $shortText;

    /**
     * @var string
     */
    private string $text;

    /**
     * @var string
     */
    private string $url;

    /**
     * @var string
     */
    private string $changedDateTime;

    /**
     * @var string|array
     */
    private array $participants;

    /**
     * @param string $_privateIdentifier
     * @param string $_shortText
     * @param string $_text
     * @param string $_url
     * @param string $_changedDateTime
     * @param array  $_participants
     */
    public function __construct(
        string $_privateIdentifier,
        string $_shortText,
        string $_text,
        string $_url,
        string $_changedDateTime,
        array $_participants = []
    ) {
        $this->privateIdentifier = $_privateIdentifier;
        $this->shortText = $_shortText;
        $this->text = $_text;
        $this->url = $_url;
        $this->changedDateTime = $_changedDateTime;
        $this->participants = $_participants;
    }

    /**
     * Function data
     *
     * @return array
     * @throws \Exception
     */
    public function data(): array
    {
        $participants = $this->prepareParticipants();

        return [
            'privateidentificationid' => $this->privateIdentifier,
            'shorttext' => $this->shortText,
            'text' => base64_encode($this->text),
            'lyric' => false,
            'involvement' => empty($participants) ? 'AUTHOR' : 'NO_PARTICIPATION',
            'participants' => $participants,
            'webranges' => [
                [
                    'urls' => [
                        $this->url
                    ]
                ]
            ],
            'textLengthChanges' => [
                [
                    'changedAt' => $this->prepareChangedAt(),
                    'textLength' => mb_strlen($this->text),
                ]
            ]
        ];
    }

    /**
     * Function prepareParticipants
     *
     * @return array
     */
    private function prepareParticipants(): array
    {
        $participants = [];

        foreach ($this->participants as $participant)
        {
            //only participants with card number
            if(empty($participant['cardNumber']))
            {
                continue;
            }

            $participants[] = [
                'involvement' => 'AUTHOR',
                'cardNumber' => (int)$participant['cardNumber'],
                'firstName' => $participant['firstName'],
                'surName' => $participant['lastName'],
            ];
        }

        return $participants;
    }

    /**
     * Function prepareChangedAt
     *
     * @return string
     * @throws \Exception
     */
    private function prepareChangedAt(): string
    {
        $date = new DateTime($this->changedDateTime);

        return $date->format('Y-m-d\TH:i:s.\0\0\0');
    }
}
