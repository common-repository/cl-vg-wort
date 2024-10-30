<?php

declare(strict_types=1);

namespace CrayssnLabsVgWort\Framework\VgWort;

use CrayssnLabsVgWort\CrayssnLabsVgWort;
use DOMDocument;
use DOMElement;
use DOMText;
use DOMXPath;

/**
 * Class Pixel
 *
 * @package   CrayssnLabsVgWort\Framework\VgWort
 *
 * @author    Sebastian Ludwig <dev@cl.team>
 * @copyright Copyright (c) 2023, CrayssnLabs Ludwig Wiegler GbR
 */
class Pixel
{
    /**
     *
     */
    public string $identifier;

    /**
     * @var ?string
     */
    public ?string $domain;

    /**
     * @var string|null
     */
    public ?string $privateIdentifier;

    /**
     * @var string|null
     */
    public ?string $publicIdentifier;

    /**
     * @var array
     */
    public array $status;

    /**
     * @var string|null
     */
    public ?string $reported;

    /**
     * @var string|null
     */
    public ?string $siteType = null;

    /**
     * @var string|null
     */
    public ?int $siteId = null;

    /**
     * @var string|null
     */
    private ?string $siteContent = null;

    /**
     * @param array $_pixelData = [
     *     "domain" => "vg09.met.vgwort.de",
     *     "publicIdentifier" => "9e3b8158bb9a4c3aaa6cc3bb81bb55f2",
     *     "privateIdentifier" => "1cc10f02f073484589ca0c242bcb41d9"
     *     ]
     */
    public function __construct(array $_pixelData)
    {
        $this->identifier = $_pixelData['identifier'];
        $this->domain = $_pixelData['domain'] ?? null;
        $this->publicIdentifier = $_pixelData['publicIdentifier'] ?? null;
        $this->privateIdentifier = $_pixelData['privateIdentifier'] ?? null;
        $this->status = $_pixelData['status'] ?? [];
        $this->reported = $_pixelData['reported'] ?? null;

        $identifierParts = explode('-', $this->identifier);

        if(count($identifierParts) !== 2)
        {
            return;
        }

        $this->siteType = $identifierParts[0];
        $this->siteId = (int)$identifierParts[1];
    }

    /**
     * Function getInstanceByArray
     *
     * @param array $_data
     *
     * @return \CrayssnLabsVgWort\Framework\VgWort\Pixel|null
     */
    public static function getInstanceByArray(array $_data): ?Pixel
    {
        if(empty($_data))
        {
            return null;
        }

        return new static($_data);
    }

    /**
     * Function getInstanceByJson
     *
     * @param string $_jsonString
     *
     * @return \CrayssnLabsVgWort\Framework\VgWort\Pixel|null
     */
    public static function getInstanceByJson(string $_jsonString): ?Pixel
    {
        $jsonArray = json_decode($_jsonString, true);

        if (!is_array($jsonArray)) {
            return null;
        }

        return self::getInstanceByArray($jsonArray);
    }

    /**
     * Function getInstanceByFile
     *
     * @param mixed $_pixelFile
     *
     * @return \CrayssnLabsVgWort\Framework\VgWort\Pixel|null
     */
    public static function getInstanceByFile($_pixelFile): ?Pixel
    {
        $_jsonString = file_get_contents($_pixelFile);

        $instance = self::getInstanceByJson($_jsonString);

        if (empty($instance)) {
            return null;
        }

        $instance->file = $_pixelFile;

        return $instance;
    }

    /**
     * Function getCounterUrl
     *
     * @return string|null
     */
    public function getCounterUrl(): ?string
    {
        if (empty($this->domain) || empty($this->publicIdentifier)) {
            return null;
        }

        return 'https://' . $this->domain . '/na/' . $this->publicIdentifier;
    }

    /**
     * Function getSiteUrl
     *
     * @return string|null
     */
    public function getSiteUrl(): ?string
    {
        if($this->siteType === 'post')
        {
            $link = get_permalink( $this->siteId );

            if($link === false)
            {
                return null;
            }

            return $link;
        }

        return null;
    }

    /**
     * Function getSiteLastUpdate
     *
     * @return string|null
     */
    public function getSiteLastUpdate(): ?string
    {
        if($this->siteType === 'post')
        {
            $post = get_post( $this->siteId );

            if(!empty($post))
            {
                return $post->post_modified;
            }
        }

        return null;
    }

    /**
     * Function getSiteAuthorName
     *
     * @return string|null
     */
    public function getSiteAuthorName(): ?string
    {
        if($authorId = $this->getSiteAuthorId())
        {
            $name = get_the_author_meta('first_name', $authorId) . ' ' . get_the_author_meta('last_name', $authorId);

            if($name === ' ')
            {
                return null;
            }

            return $name;
        }

        return null;
    }

    /**
     * Function getSiteAuthorId
     *
     * @return int|null
     */
    public function getSiteAuthorId(): ?int
    {
        if($this->siteType === 'post')
        {
            $authorId = get_post_field('post_author', $this->siteId);

            return (int)$authorId;
        }

        return null;
    }

    /**
     * Function getSiteUrl
     *
     * @return string
     */
    public function getSiteAuthorCardNumber(): ?string
    {
        if($authorId = $this->getSiteAuthorId())
        {
            $cardNumber = get_the_author_meta(CrayssnLabsVgWort::CARD_NUMBER_INDEX, $authorId);

            if(!empty($cardNumber))
            {
                return $cardNumber;
            }
        }

        return null;
    }

    /**
     * Function getSiteTitle
     *
     * @return string|null
     */
    public function getSiteTitle(): ?string
    {
        if($this->siteType === 'post')
        {
            return get_the_title( $this->siteId );
        }

        return null;
    }

    /**
     * Function getSiteMetaTitle
     *
     * @return string|null
     */
    public function getSiteMetaTitle(): ?string
    {
        $siteContent = $this->getSiteContent();

        $doc = new DOMDocument();
        $doc->loadHTML($siteContent, LIBXML_NOERROR);

        foreach ($doc->getElementsByTagName('title') as $title)
        {
            return $title->textContent;
        }

        return null;
    }

    /**
     * Function getSiteText
     *
     * @return string|null
     */
    public function getSiteText(): ?string
    {
        $siteContent = $this->getSiteContent();
        $verifyContent = '';
        $finalHtmlContent = '';

        $doc = new DOMDocument();
        $doc->loadHTML($siteContent, LIBXML_NOERROR);

        $nodeList = $doc->getElementsByTagName('*');

        if($nodeList->count() === 0)
        {
            return null;
        }

        //removed all attributes from all domelements
        foreach($nodeList as $element )
        {
            while ($element->hasAttributes())
            {
                $element->removeAttributeNode($element->attributes->item(0));
            }
        }

        $xpath = new DOMXPath($doc);
        $textnodes = $xpath->query('//text()');

        /**
         * @var DOMText $textnode
         */
        foreach($textnodes as $textnode)
        {
            if(empty(trim($textnode->textContent)))
            {
                $textnode->parentNode->removeChild($textnode);
            }
        }

        foreach (['h1', 'h2', 'h3', 'h4'] as $tag)
        {
            /**
             * @var DOMElement $element
             */
            foreach ($doc->getElementsByTagName($tag) as $element)
            {
                $headlineHtml = strip_tags($doc->saveHTML($element), '<' . $tag . '>');

                /*
                echo $tag;

                echo "<textarea>" . $doc->saveHTML($element) . "</textarea>";

                echo "<textarea>" . $headlineHtml . "</textarea>";

                echo "<hr>";
                */
                if(str_contains($verifyContent, $headlineHtml))
                {
                    continue;
                }

                $verifyContent .= $headlineHtml;

                for($max = 5; $element->parentNode->textContent === $element->textContent && $max > 0; --$max)
                {
                    $element = $element->parentNode;
                }

                $mainElement = $element->parentNode;

                foreach (['img', 'script', 'meta', 'svg'] as $removableTag)
                {
                    /**
                     * @var DOMElement $removableElement
                     */
                    foreach ($mainElement->getElementsByTagName($removableTag) as $removableElement)
                    {
                        $removableElement->parentNode->removeChild($removableElement);
                    }
                }

                /**
                 * @var DOMElement $removableTagIfEmptyElement
                 */
                foreach ($mainElement->getElementsByTagName('*') as $removableTagIfEmptyElement)
                {
                    if(empty(trim($removableTagIfEmptyElement->textContent)))
                    {
                        $removableTagIfEmptyElement->parentNode->removeChild($removableTagIfEmptyElement);
                    }
                }

                $html = trim($doc->saveHTML($mainElement));

                if(str_contains($verifyContent, $html))
                {
                    continue;
                }

                $verifyContent .= $html;

                $html = strip_tags($html, '<h1><h2><h3><h4><p><ol><ul><li><hr>');

                //echo "<textarea>" . $headlineHtml . "</textarea>";

                if(strpos($html, $headlineHtml) === false)
                {
                    $finalHtmlContent .= trim($html);
                }
                else
                {
                    $finalHtmlContent .= trim(substr($html, strpos($html, $headlineHtml)));
                }
            }
        }

        //echo "<h1>Final version (with tags) " . $this->getSiteUrl() . "</h1>";
        //echo "<textarea style='width: 100%;height: 20rem;'>" . $finalHtmlContent . "</textarea>";

        $finalHtmlContent = str_replace(["<p> </p>","\n\n\n\n","\n\n\n","\n\n"], "\n", $finalHtmlContent);
        $finalHtmlContent = str_replace(['.', '?', '!', ')', '>', '<'], ['. ', '? ', '! ', ') ',  '> ', ' <'], $finalHtmlContent);
        $finalHtmlContent = strip_tags($finalHtmlContent);
        $finalHtmlContent = str_replace(["\n", '  ', '  ', '  '], ' ', $finalHtmlContent);

        //echo "<h1>Final version (text only) " . $this->getSiteUrl() . "</h1>";
        //echo "<textarea style='width: 100%;height: 20rem;'>" . $finalHtmlContent . "</textarea>";

        return trim($finalHtmlContent);
    }

    /**
     * Function getParticipants
     *
     * @return array
     */
    public function getParticipants(): array
    {
        $post = get_post( $this->siteId );

        if(!empty($post->post_author))
        {
            $cardNumber = get_the_author_meta(CrayssnLabsVgWort::CARD_NUMBER_INDEX, $post->post_author);

            if(!empty($cardNumber))
            {
                $firstName = get_the_author_meta('first_name', $post->post_author);
                $lastName = get_the_author_meta('last_name', $post->post_author);

                return [
                    [
                        'firstName' => $firstName,
                        'lastName' => $lastName,
                        'cardNumber' => $cardNumber,
                    ],
                ];
            }
        }

        return [];
    }

    /**
     * Function getSiteContent
     *
     * @return string
     */
    private function getSiteContent(): string
    {
        if(empty($this->siteContent))
        {
            $this->siteContent = file_get_contents($this->getSiteUrl());
        }

        return $this->siteContent;
    }

    /**
     * Function getSiteStatus
     *
     * @return false|string
     */
    public function getSiteStatus()
    {
        return get_post_status( $this->siteId );
    }
}
